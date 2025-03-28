<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ModelFactory;
use MF\Model\ReviewModelFactory;
use UnexpectedValueException;

class ReviewRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private SessionManager $session,
        private ReviewModelFactory $model,
        private ModelFactory $modelFactory,
    ) {
    }

    public function add(AppObject $entity): string
    {
        $prunedEntity = $this->em->pruneAppObject(
            $entity,
            $this->modelFactory->getReviewModel(false),
        );
        $this->dbManager->runFilename(
            'stmt_add_review.sql',
            $this->em->toDbValue($prunedEntity, ignoreProperties: ['id']),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_review WHERE review_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM v_review WHERE (review_id = ?) LIMIT 1;', [$id]);

        if (0 === count($dbRows)) {
            return null;
        } elseif (1 === count($dbRows)) {
            $model = $this->modelFactory->getReviewModel(playable: true, gameIfPlayable: false);
            return $this->em->convertDbRowsToAppObject($dbRows, $model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): AppList
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM v_article WHERE review_id IS NOT NULL;');
        $model = $this->modelFactory->getReviewModel(playable: true);
        return $this->em->convertDbRowsToEntityList($dbRows, $model);
    }

    public function update(AppObject $entity, string $persistedId): void
    {
        $prunedEntity = $this->em->pruneAppObject(
            $entity,
            $this->modelFactory->getReviewModel(false),
        );
        $this->dbManager->runFilename(
            'stmt_update_review.sql',
            $this->em->toDbValue($prunedEntity, ignoreProperties: ['id']) + ['persisted_id' => $persistedId ?? $entity['id']]
        );
    }
}