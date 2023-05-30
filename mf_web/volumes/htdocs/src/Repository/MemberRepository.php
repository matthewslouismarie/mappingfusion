<?php

namespace MF\Repository;

use MF\Model\Member;
use PDO;

class MemberRepository
{
    public function __construct(
        private PDO $pdo,
    ) {
    }

    public function add(Member $member): void {
        $this->pdo->prepare('')
    }
}