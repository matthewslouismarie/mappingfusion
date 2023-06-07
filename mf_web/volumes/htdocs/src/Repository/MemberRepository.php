<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Member;
use MF\Model\PasswordHash;
use UnexpectedValueException;

class MemberRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Member $member): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_member VALUES (:username, :password)');
        $stmt->execute(['username' => $member->getId(), 'password' => $member->getPasswordHash()]);
    }

    public function find(string $username): ?Member {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_member WHERE (member_id=:username) LIMIT 1');
        $stmt->execute(['username' => $username]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return new Member($data[0]['member_id'], new PasswordHash(hash: $data[0]['member_password']));
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function updateMember(Member $member): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_member SET member_password = :password WHERE member_id = :username');
        $stmt->execute(['password' => $member->getPasswordHash(), 'username' => $member->getId()]);
    }
}