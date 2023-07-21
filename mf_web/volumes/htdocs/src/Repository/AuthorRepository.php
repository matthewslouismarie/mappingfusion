<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Model\Author;
use UnexpectedValueException;

class AuthorRepository
{
    public function __construct(
        private DatabaseManager $conn,
    ) {
    }

    public function add(Author $author): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_author VALUES (:author_id, :author_name);');
        $stmt->execute($author->toArray());
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_author WHERE author_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Author {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_author WHERE author_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return Author::fromArray($data[0]);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_author;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = Author::fromArray($r);
        }
        return $entities;
    }

    public function findAuthorsOf(string $playableId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;');
        $stmt->execute([$playableId]);
        $row = $stmt->fetch();
        $authors = [];
        while (false !== $row) {
            $authors[] = Author::fromArray($row);
            $row = $stmt->fetch();
        }

        return $authors;
    }

    public function update(string $previousId, Author $author): void {
        if ($previousId === $author->getId()) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_author SET author_name = :author_name WHERE author_id = :author_id;');
            $stmt->execute($author->toArray());
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($author);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}