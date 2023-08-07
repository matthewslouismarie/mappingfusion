<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\DataStructure\AppObject;
use MF\Database\DbEntityManager;
use MF\Model\AuthorModel;
use UnexpectedValueException;

class AuthorRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private AuthorModel $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(array $authorAppArray): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_author VALUES (:id, :name);');
        $stmt->execute($this->em->toDbValue($authorAppArray));
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_author WHERE author_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_author WHERE author_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return $this->em->toScalarArray($data[0], 'author');
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_author;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toScalarArray($r, 'author');
        }
        return $entities;
    }

    public function findAuthorsOf(string $playableId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;');
        $stmt->execute([$playableId]);
        $row = $stmt->fetch();
        $authors = [];
        while (false !== $row) {
            $authors[] = $this->em->toScalarArray($row, 'author');
            $row = $stmt->fetch();
        }

        return $authors;
    }

    public function findOne(string $id): array {
        return $this->find($id);
    }

    public function update(string $previousId, array $authorAppArray): void {
        if ($previousId === $authorAppArray['id']) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_author SET author_name = :name WHERE author_id = :id;');
            $stmt->execute($this->em->toDbValue($authorAppArray));
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($authorAppArray);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}