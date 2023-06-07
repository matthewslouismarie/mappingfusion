<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Member;
use UnexpectedValueException;

class MemberRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Member $member): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO t_member VALUES (:username, :password)');
        $stmt->execute(['username' => $member->getUsername(), 'password' => $member->getPasswordHash()]);
    }

    public function find(string $username): ?Member {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM t_member WHERE (member_username=:username) LIMIT 1');
        $stmt->execute(['username' => $username]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return new Member($data[0]['member_password'], $data[0]['member_username']);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function updateMember(Member $member): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE t_member SET member_password = :password WHERE member_username = :username');
        $stmt->execute(['password' => $member->getPasswordHash(), 'username' => $member->getUsername()]);
    }
}