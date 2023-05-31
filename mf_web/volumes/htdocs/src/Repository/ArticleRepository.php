<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Article;

class ArticleRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Article $article): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article VALUES (:p_id, :p_author, :p_title, :p_content, :p_creation_datetime, :p_last_update_datetime)');
        $stmt->execute($article->toArray());
    }

    public function find(string $id): ?Article {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_article WHERE (p_id=:id) LIMIT 1');
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return Article::fromArray($data[0]);
        } else {
            throw new UnexpectedValueException();
        }
    }
}