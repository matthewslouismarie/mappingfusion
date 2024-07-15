<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\AuthorModel;
use MF\Model\MemberModel;
use UnexpectedValueException;

class MemberRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private MemberModel $model,
    ) {
    }

    public function add(AppObject $member): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_member VALUES (:id, :password, :author_id )');
        $stmt->execute($this->em->toDbValue($member));
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
        $this->conn->run(
            'DELETE FROM e_member WHERE member_id = :id;',
            [
                'id' => $id,
            ],
        );
    }

    public function find(string $username): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_member LEFT JOIN e_author ON member_author_id = author_id WHERE (member_id=?) LIMIT 1');
        $stmt->execute([$username]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            $model = null !== $data[0]['author_id'] ? new MemberModel(new AuthorModel()) : new MemberModel();
            return $this->em->toAppData($data[0], $model, 'member');
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function update(AppObject $entity, string $oldId, bool $updatePassword = true): void {
        if ($updatePassword) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_id = :id, member_password = :password, member_author_id = :author_id WHERE member_id = :old_id;');
        } else {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_id = :id, member_author_id = :author_id WHERE member_id = :old_id;');
        }
        $stmt->execute($this->em->toDbValue($entity) + [$oldId]);
    }

    public function updateId(string $oldId, string $newId): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_id = :new_id WHERE member_id = :old_id');
        $stmt->execute(['old_id' => $oldId, 'new_id' => $newId]);
    }
}