<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\ListModel;
use MF\Model\AuthorModelFactory;
use MF\Model\MemberModelFactory;
use UnexpectedValueException;

class AuthorRepository implements IRepository
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private MemberModelFactory $memberModelFactory,
    ) {
    }

    public function add(AppObject $author): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_author VALUES (:id, :name, :avatar_filename);');
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

        $authorRows = $stmt->fetchAll();
        if (0 === count($authorRows)) {
            return null;
        } elseif (1 === count($authorRows)) {
            return $this->em->convertDbRowsToAppObject($authorRows, $this->authorModelFactory->create($this->memberModelFactory->create(isNullable: true)), 'author');
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array
    {
        $authorRows = $this->conn->fetchRows('SELECT * FROM e_author;');
        
        return $this->em->convertDbList($authorRows, new ListModel($this->authorModelFactory->create()));
    }

    public function findAuthorsOf(string $playableId): array {
        $dbRows = $this->conn->fetchRows('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;', [$playableId]);

        return $this->em->convertDbList($dbRows, new ListModel($this->authorModelFactory->create()));
    }

    public function findOne(string $id): AppObject {
        return $this->find($id);
    }

    public function update(AppObject $author, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_author SET author_id = :id, author_name = :name, author_avatar_filename = :avatar_filename WHERE author_id = :previous_id;');
        $stmt->execute(['previous_id' => $previousId ?? $author->id] + $this->em->toDbValue($author));
    }
}