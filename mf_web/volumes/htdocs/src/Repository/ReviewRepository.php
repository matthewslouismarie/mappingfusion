<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Database\DbEntityManager;
use MF\Model\PlayableModel;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ReviewModel;
use UnexpectedValueException;

class ReviewRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private SessionManager $session,
        private ReviewModel $model,
    ) {
    }

    public function add(AppObject $review): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_review VALUES (:id, :article_id, :playable_id, :rating, :body, :cons, :pros);');
        $stmt->execute($this->em->toDbValue($review));
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(int $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_review WHERE review_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_review WHERE (review_id = ?) LIMIT 1;');
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
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;')->fetchAll();
        $reviews = [];
        foreach ($results as $row) {
            $reviews[] = $this->em->toAppData($row, new ReviewModel(new PlayableModel()), 'review');
        }
        return $reviews;
    }

    public function update(AppObject $review): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_review SET review_article_id = :article_id, review_playable_id = :playable_id, review_rating = :rating, review_body = :body, review_cons = :cons, review_pros = :pros WHERE review_id = :id;');
        $stmt->execute($this->em->toDbValue($review));
    }
}