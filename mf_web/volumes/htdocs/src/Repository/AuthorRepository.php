<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\DataStructure\AppObject;
use MF\Entity\DbEntityManager;
use MF\Model\Author;
use MF\Model\AuthorDefinition;
use UnexpectedValueException;

class AuthorRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private AuthorDefinition $def,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $author): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_author VALUES (:author_id, :author_name);');
        $stmt->execute($this->em->toDbArray($author, $this->def));
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_author WHERE author_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_author WHERE author_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return $this->em->toAppArray($data[0], $this->def);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_author;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppArray($r, $this->def);
        }
        return $entities;
    }

    public function findAuthorsOf(string $playableId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;');
        $stmt->execute([$playableId]);
        $row = $stmt->fetch();
        $authors = [];
        while (false !== $row) {
            $authors[] = $this->em->toAppArray($row, $this->def);
            $row = $stmt->fetch();
        }

        return $authors;
    }

    public function update(string $previousId, AppObject $author): void {
        if ($previousId === $author->id) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_author SET author_name = :author_name WHERE author_id = :author_id;');
            $stmt->execute($this->em->toDbArray($author, $this->def));
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($author);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}