<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Member;

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
}