<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObject;
use MF\Model\ContributionModel;

class ContributionRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private ContributionModel $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $contribution): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_contribution VALUES (:id, :author_id, :playable_id, :is_author, :summary);');
        $stmt->execute($this->em->toDbArray($contribution, $this->model));
    }

    public function delete(int $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_contribution WHERE contribution_id = :?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_contribution WHERE contribution_id = :?;');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return null !== $data ? $this->em->toAppObject($data, $this->model, ['contribution_' => null]) : null;
    }
}