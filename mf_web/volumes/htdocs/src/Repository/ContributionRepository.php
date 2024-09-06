<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\ContributionModelFactory;

/**
 * @todo Should implement a repository that does not allow ID update.
 */
class ContributionRepository implements IConstIdRepository
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
        $dbRows = $this->dbManager->fetchRows(
            'SELECT * FROM e_contribution WHERE contribution_id = ?;',
            [$id],
        );
        return 0 !== count($dbRows) ? $this->em->convertDbRowsToAppObject($dbRows, $this->model->create()) : null;
    }

    public function filterOutPlayableContributions(string $playableId, array $ids): void {
        if (0 === count($ids)) {
            $this->dbManager->run(
                "DELETE FROM e_contribution WHERE contribution_playable_id = ?;",
                [$playableId],
            );
        } else {
            $inQuery = str_repeat('?,', count($ids) - 1) . '?';
            $this->dbManager->run(
                "DELETE FROM e_contribution WHERE contribution_playable_id = ? AND contribution_id NOT IN ($inQuery);",
                array_merge_recursive([$playableId], $ids),
            );
        }
    }

    public function update(AppObject $entity): void
    {
        $dbArray = $this->em->toDbValue($entity);
        $this->dbManager->run(
            'UPDATE e_contribution SET contribution_author_id = :author_id, contribution_playable_id = :playable_id, contribution_is_author = :is_author, contribution_summary = :summary WHERE contribution_id = :id;',
            $dbArray,
        );
    }
}