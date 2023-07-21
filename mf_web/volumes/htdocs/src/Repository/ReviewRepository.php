<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\HttpBridge\Session;
use MF\Model\Review;
use UnexpectedValueException;

class ReviewRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private Session $session,
    ) {
    }

    public function add(Review $entity): Review {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_review VALUES (NULL, ?, ?, ?, ?, ?, ?);');
        $stmt->execute([$entity->getArticleId(), $entity->getPlayableId(), $entity->getRating(), $entity->getBody(), $entity->getCons(), $entity->getPros()]);
        $newId = $this->conn->getPdo()->lastInsertId();
        return Review::fromArray(['review_id' => $newId] + $entity->toArray());
    }

    public function delete(int $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_review WHERE review_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Review {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_review WHERE (review_id = ?) LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return Review::fromArray($data[0]);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = Review::fromArray($r);
        }
        return $entities;
    }

    public function update(Review $entity): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_review SET review_article_id = :review_article_id, review_playable_id = :review_playable_id, review_rating = :review_rating, review_body = :review_body, review_cons = :review_cons, review_pros = :review_pros WHERE review_id = :review_id;');
        $stmt->execute($entity->toArray());
    }
}