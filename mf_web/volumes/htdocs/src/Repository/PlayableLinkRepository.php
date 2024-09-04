<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\ModelFactory;
use MF\Model\PlayableLinkModelFactory;
use MF\Repository\Exception\EntityNotFoundException;

class PlayableLinkRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private ModelFactory $modelFactory,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $link): string {
        $this->dbManager->run(
            'INSERT INTO e_playable_link VALUES (:id, :playable_id, :name, :type, :url);',
            $this->em->toDbValue($link),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run(
            'DELETE FROM e_playable_link WHERE link_id = :id;',
            [
                'id' => $id,
            ],
        );
    }

    public function find(string $id): AppObject
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_playable_link WHERE link_id = ?;', [$id]);
        if (0 === count($dbRows)) {
            throw new EntityNotFoundException();
        }
        $model = $this->modelFactory->getPlayableLinkModel();
        return $this->em->convertDbRowsToAppObject($dbRows, $model);
    }

    public function remove(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_playable_link WHERE link_id = ?;', [$id]);
    }

    public function filterOutPlayableLinks(string $playableId, array $linkIds): void {
        if (0 === count($linkIds)) {
            $this->dbManager->run('DELETE FROM e_playable_link WHERE link_playable_id = ?;', [$playableId]);
        } else {
            $inQuery = str_repeat('?,', count($linkIds) - 1) . '?';
            $this->dbManager->run(
                "DELETE FROM e_playable_link WHERE link_playable_id = ? AND link_id NOT IN ({$inQuery});",
                array_merge_recursive([$playableId], $linkIds),
            );
        }
    }

    public function update(AppObject $entity, ?string $previousId = null): void
    {
        $this->dbManager->runFilename(
            'stmt_update_playable_link.sql',
            $this->em->toDbValue($entity) + ['previous_id' => $previousId ?? $entity['id']],
        );
    }
}