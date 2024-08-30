<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\ContributionModelFactory;

class ContributionRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private ContributionModelFactory $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $contrib): string
    {
        $this->dbManager->run(
            'INSERT INTO e_contribution VALUES (:id, :author_id, :playable_id, :is_author, :summary);',
            $this->em->toDbValue($contrib),
        );

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_contribution WHERE contribution_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $stmt = $this->dbManager->getPdo()->prepare('SELECT * FROM e_contribution WHERE contribution_id = ?;');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return null !== $data ? $this->em->toAppData($data, $this->model, 'contribution') : null;
    }

    public function filterOutPlayableContributions(string $playableId, array $ids): void {
        if (0 === count($ids)) {
            $delStmt = $this->dbManager->getPdo()->prepare("DELETE FROM e_contribution WHERE contribution_playable_id = ?;");
            $delStmt->execute([$playableId]);
        } else {
            $inQuery = str_repeat('?,', count($ids) - 1) . '?';
            $delStmt = $this->dbManager->getPdo()->prepare("DELETE FROM e_contribution WHERE contribution_playable_id = ? AND contribution_id NOT IN ($inQuery);");
            $delStmt->execute(array_merge_recursive([$playableId], $ids));
        }
    }

    public function update(AppObject $contrib, ?string $previousId = null): void {
        $dbArray = $this->em->toDbValue($contrib);
        $stmt = $this->dbManager->getPdo()->prepare('UPDATE e_contribution SET contribution_id = :id, contribution_author_id = :author_id, contribution_playable_id = :playable_id, contribution_is_author = :is_author, contribution_summary = :summary WHERE contribution_id = :previous_id;');
        $stmt->execute($dbArray + ['previous_id' => $previousId ?? $dbArray['id']]);
    }
}