<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\HttpBridge\Session;
use MF\Model\Article;

class ArticleRepository
{
    public function __construct(
        private Connection $conn,
        private Session $session,
    ) {
    }

    public function addNewArticle(Article $article): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW());');
        $stmt->execute([$article->getId(), $this->session->getCurrentMemberUsername(), $article->getContent(), $article->isFeatured(), $article->isReview(), $article->getTitle(), $article->getCoverFilename()]);
    }

    public function deleteArticle(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_article WHERE p_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Article {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_article WHERE (p_id = ?) LIMIT 1;');
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

    public function findFeatured(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_article WHERE p_is_featured = 1;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function findReviews(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_article WHERE p_review_id != NULL;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = Article::fromArray($article);
        }
        return $articles;
    }

    public function updateArticle(string $previousId, Article $article, bool $updateCoverFilename = true): void {
        if ($previousId === $article->getId()) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET p_content = ?, p_is_featured = ?, p_review_id = ?, p_title = ?, ' . ($updateCoverFilename ? 'p_cover_filename = ?, ' : '') . 'p_last_update_datetime = NOW() WHERE p_id = ?;');
            $parameters = [$article->getContent(), $article->isFeatured() ? 1 : 0, $article->getReviewId(), $article->getTitle()];
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