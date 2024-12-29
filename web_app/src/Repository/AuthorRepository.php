<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\DataStructure\SqlFilename;
use MF\Model\AuthorModelFactory;
use MF\Model\MemberModelFactory;
use UnexpectedValueException;

class AuthorRepository implements IUpdatableIdRepository
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private MemberModelFactory $memberModelFactory,
    ) {
    }

    public function add(AppObject $author): string
    {
        $this->dbManager->run(
            'INSERT INTO e_author VALUES (:id, :name, :avatar_filename);',
            $this->em->toDbValue($author),
        );

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->dbManager->run('DELETE FROM e_author WHERE author_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_author.sql'), [$id]);

        if (0 === count($dbRows)) {
            return null;
        } elseif (1 === count($dbRows)) {
            $model = $this->authorModelFactory->create($this->memberModelFactory->create());
            return $this->em->convertDbRowsToAppObject($dbRows, $model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array
    {
        $authorRows = $this->dbManager->fetchRows('SELECT * FROM e_author;');
        
        return $this->em->convertDbRowsToEntityList($authorRows, $this->authorModelFactory->create());
    }

    public function findAuthorsOf(string $playableId): array {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_contribution LEFT JOIN e_author ON contribution_author_id = author_id WHERE contribution_playable_id = ? AND contribution_is_author;', [$playableId]);

        return $this->em->convertDbRowsToEntityList($dbRows, $this->authorModelFactory->create());
    }

    public function findOne(string $id): AppObject {
        return $this->find($id);
    }

    public function update(AppObject $author, ?string $persistedId = null): void
    {
        $this->dbManager->runFilename(
            'stmt_update_author.sql',
            ['persisted_id' => $persistedId ?? $author['id']] + $this->em->toDbValue($author),
        );
    }
}