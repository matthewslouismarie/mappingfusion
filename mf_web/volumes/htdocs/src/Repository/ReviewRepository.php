<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Database\DbEntityManager;
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
        $this->dbManager->run(
            'INSERT INTO e_review VALUES (:id, :article_id, :playable_id, :rating, :body, :cons, :pros);',
            $this->em->toDbValue($entity),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_review WHERE review_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_review WHERE (review_id = ?) LIMIT 1;', [$id]);

        if (0 === count($dbRows)) {
            return null;
        } elseif (1 === count($dbRows)) {
            $model = $this->modelFactory->getReviewModel();
            return $this->em->convertDbRowsToAppObject($dbRows, $model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM v_article WHERE review_id IS NOT NULL;');
        $model = $this->modelFactory->getReviewModel(playable: true);
        return $this->em->convertDbRowsToEntityList($dbRows, $model);
    }

    public function update(AppObject $entity, ?string $previousId = null): void
    {

        $this->dbManager->runFilename(
            'stmt_update_review.sql',
            $this->em->toDbValue($entity->removeProperty('id')) + ['previous_id' => $previous_id ?? $entity['id']]
        );
    }
}