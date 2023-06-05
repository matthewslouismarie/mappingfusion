<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\HttpBridge\Session;
use MF\Model\Review;
use UnexpectedValueException;

class ReviewRepository
{
    public function __construct(
        private Connection $conn,
        private Session $session,
    ) {
    }

    public function add(Review $entity): Review {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_review VALUES (NULL, ?, ?, ?, ?, ?);');
        $stmt->execute([$entity->getPlayableId(), $entity->getRating(), $entity->getBody(), $entity->getCons(), $entity->getPros()]);
        $newId = $this->conn->getPdo()->lastInsertId();
        return Review::fromArray(['p_id' => $newId] + $entity->toArray());
    }

    public function delete(int $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_review WHERE p_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Review {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_review WHERE (p_id = ?) LIMIT 1;');
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
        $results = $this->conn->getPdo()->query('SELECT * FROM e_review;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = Review::fromArray($r);
        }
        return $entities;
    }

    public function update(Review $entity): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_review SET p_playable_id = :p_playable_id, p_rating = :p_rating, p_body = :p_body, p_cons = :p_cons, p_pros = :p_pros WHERE p_id = :p_id;');
        $stmt->execute($entity->toArray());
    }
}