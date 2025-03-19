<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\AuthorModelFactory;
use MF\Model\MemberModelFactory;
use UnexpectedValueException;

class MemberRepository implements IUpdatableIdRepository
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private MemberModelFactory $memberModelFactory,
    ) {
    }

    public function add(AppObject $member): string
    {
        $this->dbManager->run(
            'INSERT INTO e_member VALUES (:id, :password, :author_id, UUID());',
            $this->em->toDbValue($member),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run(
            'DELETE FROM e_member WHERE member_id = :id;',
            [
                'id' => $id,
            ],
        );
    }

    public function find(string $username): ?AppObject
    {
        $data = $this->dbManager->fetchRows(
            'SELECT * FROM e_member LEFT JOIN e_author ON member_author_id = author_id WHERE (member_id=?) LIMIT 1;',
            [$username],
        );

        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            if (null === $data[0]['author_id']) {
                $model = $this->memberModelFactory->create();
            } else {
                $model = $this->memberModelFactory->create($this->authorModelFactory->create());
            }
            return $this->em->convertDbRowsToAppObject($data, $model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function update(AppObject $entity, string $persistedId): void
    {
        $dbData = $this->em->toDbValue($entity);
        $dbData['password'] = password_hash($dbData['password'], PASSWORD_DEFAULT);
        $this->dbManager->runFilename('stmt_update_account.sql', $dbData + ['persisted_id' => $persistedId]);
    }

    public function updateExceptPassword(AppObject $entity, string $persistedId): void
    {
        $dbData = $this->em->toDbValue($entity, ignoreProperties: ['password']);
        $this->dbManager->runFilename('stmt_update_account_except_password.sql', $dbData + ['persisted_id' => $persistedId]);
    }
}