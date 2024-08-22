<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Database\DbEntityManager;
use MF\Model\PlayableModelFactory;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ReviewModelFactory;
use UnexpectedValueException;

class ReviewRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private SessionManager $session,
        private ReviewModelFactory $model,
    ) {
    }

    public function add(AppObject $review): string
    {
        $this->dbManager->run(
            'INSERT INTO e_review VALUES (:id, :article_id, :playable_id, :rating, :body, :cons, :pros);',
            $this->em->toDbValue($review),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->dbManager->getPdo()->prepare('DELETE FROM e_review WHERE review_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->dbManager->getPdo()->prepare('SELECT * FROM e_review WHERE (review_id = ?) LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return $this->em->toAppData($data[0], $this->model, 'review');
        } else {
            throw new UnexpectedValueException();
        }
    }

    /**
     * @return \LM\WebFramework\DataStructures\AppObject[]
     */
    public function findAll(): array {
        $results = $this->dbManager->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;')->fetchAll();
        $reviews = [];
        foreach ($results as $row) {
            $reviews[] = $this->em->toAppData($row, new ReviewModelFactory(new PlayableModelFactory()), 'review');
        }
        return $reviews;
    }

    public function update(AppObject $review, ?string $previousId = null): void {
        $stmt = $this->dbManager->getPdo()->prepare('UPDATE e_review SET review_article_id = :article_id, review_playable_id = :playable_id, review_rating = :rating, review_body = :body, review_cons = :cons, review_pros = :pros WHERE review_id = :previous_id;');
        $stmt->execute($this->em->toDbValue($review) + ['previous_id' => $previous_id ?? $review['id']]);
    }
}