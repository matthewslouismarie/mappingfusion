<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\HttpBridge\Session;
use MF\Model\Article;
use OutOfBoundsException;
use UnexpectedValueException;

class ArticleRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private Session $session,
    ) {
    }

    public function addNewArticle(Article $entity): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW());');
        $stmt->execute([$entity->getId(), $entity->getAuthorUsername(), $entity->getCategoryId(), $entity->getContent(), $entity->isFeatured() ? 1 : 0, $entity->getTitle(), $entity->getCoverFilename()]);
    }

    public function deleteArticle(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_article WHERE article_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Article {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_article WHERE article_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return Article::fromArray($data[0]);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAvailableArticles(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = Article::fromArray($r);
        }
        return $entities;
    }

    public function findOne(string $id): Article {
        $article = $this->find($id);
        if (null === $article) {
            throw new OutOfBoundsException();
        }
        return $article;
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = Article::fromArray($r);
        }
        return $entities;
    }

    public function findFeatured(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_article WHERE article_is_featured = 1;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function findLast(int $limit = 8, bool $onlyReviews = false): array {
        $whereClause = $onlyReviews ? 'WHERE article_review_id IS NOT NULL' : '';
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article {$whereClause} ORDER BY article_last_update_date_time DESC LIMIT {$limit};");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function findLastReviews(): array {
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE review_id IS NOT NULL ORDER BY article_last_update_date_time DESC LIMIT 4;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function findReviews(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function updateArticle(string $previousId, Article $article, bool $updateCoverFilename = true): void {
        if ($previousId === $article->getId()) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET article_category_id = ?, article_body = ?, article_is_featured = ?, article_title = ?, ' . ($updateCoverFilename ? 'article_cover_filename = ?, ' : '') . 'article_last_update_date_time = NOW() WHERE article_id = ?;');
            $parameters = [$article->getCategoryId(), $article->getContent(), $article->isFeatured() ? 1 : 0, $article->getTitle()];
            if ($updateCoverFilename) {
                $parameters[] = $article->getCoverFilename();
            }
            $parameters[] = $article->getId();
            $stmt->execute($parameters);
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->addNewArticle($article);
            $this->deleteArticle($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}