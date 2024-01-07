<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\AuthorModel;
use MF\Model\MemberModel;
use UnexpectedValueException;

class AuthorRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private AuthorModel $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $author): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_author VALUES (:id, :name, :avatar);');
        $stmt->execute($this->em->toDbValue($author));
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_author WHERE author_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_author LEFT JOIN e_member ON author_id = member_author_id WHERE author_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            $row = $data[0];
            $memberModel = null !== $row['member_id'] ? new MemberModel() : null;
            $author = $this->em->toAppData($row, new AuthorModel($memberModel), 'author');
            if (null === $memberModel) {
                return $author->set('member', null);
            } else {
                return $author;
            }
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_author;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, $this->model, 'author');
        }
        return $entities;
    }

    public function findAuthorsOf(string $playableId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;');
        $stmt->execute([$playableId]);
        $row = $stmt->fetch();
        $authors = [];
        while (false !== $row) {
            $authors[] = $this->em->toAppData($row, $this->model);
            $row = $stmt->fetch();
        }

        return $authors;
    }

    public function findOne(string $id): AppObject {
        return $this->find($id);
    }

    public function update(AppObject $author, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_author SET author_id = :id, author_name = :name, author_avatar_filename = :avatar_filename WHERE author_id = :previous_id;');
        $stmt->execute(['previous_id' => $previousId ?? $author->id] + $this->em->toDbValue($author));
    }
}