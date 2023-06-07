<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Contribution;

class ContributionRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Contribution $contribution): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_contribution VALUES (:contribution_id, :contribution_author_id, :contribution_playable_id, :contribution_is_author, :contribution_summary);');
        $stmt->execute($contribution->toArray());
    }

    public function delete(int $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_contribution WHERE contribution_id = :?;');
        $stmt->execute([$id]);
    }
}