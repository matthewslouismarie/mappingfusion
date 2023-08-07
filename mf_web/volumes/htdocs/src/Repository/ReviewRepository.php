<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\DataStructure\AppObject;
use MF\Database\DbEntityManager;
use MF\Session\SessionManager;
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

    public function add(array $reviewScalarArray): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_review VALUES (:id, :article_id, :playable_id, :rating, :body, :cons, :pros);');
        $stmt->execute($this->em->toDbValue($reviewScalarArray));
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
            return new AppObject($this->em->toScalarArray($data[0], 'review'), $this->model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = new AppObject($this->em->toScalarArray($r, 'review'), $this->model);
        }
        return $entities;
    }

    public function update(AppObject $entity): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_review SET review_article_id = :review_article_id, review_playable_id = :review_playable_id, review_rating = :review_rating, review_body = :review_body, review_cons = :review_cons, review_pros = :review_pros WHERE review_id = :review_id;');
        $stmt->execute($this->em->toDbValue($entity));
    }
}