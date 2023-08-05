<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\DataStructure\AppObject;
use MF\Entity\DbEntityManager;
use MF\Http\SessionManager;
use MF\Model\ArticleDefinition;
use MF\Model\PlayableDefinition;
use MF\Model\Review;
use MF\Model\ReviewDefinition;
use UnexpectedValueException;

class ReviewRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private SessionManager $session,
        private ReviewDefinition $def,
    ) {
    }

    public function add(AppObject $entity): AppObject {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_review VALUES (NULL, ?, ?, ?, ?, ?, ?);');
        $stmt->execute($this->em->toDbArray($entity, new ReviewDefinition()));
        $newId = $this->conn->getPdo()->lastInsertId();
        return $entity->set('id', $newId);
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
            return $this->em->toAppArray($data[0], new ReviewDefinition());
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppArray($r, $this->def, 'review_', childrenToProcess: [
                'stored_article' => new ArticleDefinition($this->session),
                'stored_playable' => new PlayableDefinition(),
            ]);
        }
        return $entities;
    }

    public function update(AppObject $entity): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_review SET review_article_id = :review_article_id, review_playable_id = :review_playable_id, review_rating = :review_rating, review_body = :review_body, review_cons = :review_cons, review_pros = :review_pros WHERE review_id = :review_id;');
        $stmt->execute($this->em->toDbArray($entity, new ReviewDefinition()));
    }
}