<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructures\AppObject;
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

    public function add(AppObject $member): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_member VALUES (:id, :password)');
        $stmt->execute($this->em->toDbValue($member));
    }

    public function find(string $username): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_member WHERE (member_id=?) LIMIT 1');
        $stmt->execute([$username]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return $this->em->toAppData($data[0], $this->model, 'member');
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function updateMember(AppObject $member, string $oldId): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_id = ?, member_password = ? WHERE member_id = ?;');
        $stmt->execute([$member->id, $member->password, $oldId]);
    }

    public function updateId(string $oldId, string $newId): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_id = :new_id WHERE member_id = :old_id');
        $stmt->execute(['old_id' => $oldId, 'new_id' => $newId]);
    }
}