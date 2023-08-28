<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObject;
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

    public function add(array $member): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_member VALUES (:id, :password_hash)');
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

    public function updateMember(AppObject $member): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_password = :password_hash WHERE member_id = :id');
        $stmt->execute($this->em->toDbValue($member));
    }
}