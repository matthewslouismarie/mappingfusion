<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\DataStructure\AppObject;
use MF\Entity\DbEntityManager;
use MF\Http\SessionManager;
use MF\Model\ArticleDefinition;
use MF\Model\CategoryDefinition;
use MF\Model\ModelDefinition;
use MF\Model\PlayableDefinition;
use MF\Model\ReviewDefinition;
use OutOfBoundsException;
use UnexpectedValueException;

class ArticleRepository
{
    public function __construct(
        private ArticleDefinition $def,
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private SessionManager $session,
    ) {
    }

    public function addNewArticle(AppObject $appObject): void {
        $dbArray = $this->em->toDbArray($appObject, $this->def);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW());');
        $stmt->execute([$dbArray['id'], $dbArray['author_id'], $dbArray['category_id'], $dbArray['body'], $dbArray['is_featured'], $dbArray['title'], $dbArray['cover_filename']]);
    }

    public function deleteArticle(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_article WHERE article_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_article WHERE article_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            $stored = null === $data[0]['review_id'] ? [] : [
                'stored_review' => new ReviewDefinition(),
                'stored_playable' => new PlayableDefinition(),
            ];
            return $this->em->toAppArray(
                $data[0],
                $this->def,
                'article_',
                childrenToProcess: $stored,
            );
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAvailableArticles(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppArray($r, $this->def, 'article_');
        }
        return $entities;
    }

    public function findOne(string $id): AppObject {
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
            $entities[] = $this->em->toAppArray($r, $this->def, 'article_', childrenToProcess: [
                'stored_category' => new CategoryDefinition(),
            ]);
        }
        return $entities;
    }

    public function findFeatured(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_article WHERE article_is_featured = 1;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppArray($article, $this->def, 'article_');
        }
        return $articles;
    }

    public function findLast(int $limit = 8, bool $onlyReviews = false): array {
        $whereClause = $onlyReviews ? 'WHERE article_review_id IS NOT NULL' : '';
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article {$whereClause} ORDER BY article_last_update_date_time DESC LIMIT {$limit};");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppArray($article, $this->def, 'article_', childrenToProcess: [
                'stored_category' => new CategoryDefinition('category'),
            ]);
        }
        return $articles;
    }

    public function findLastReviews(): array {
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE review_id IS NOT NULL ORDER BY article_last_update_date_time DESC LIMIT 4;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppArray($article, $this->def, 'article_', childrenToProcess: [
                'stored_playable' => new PlayableDefinition('playable'),
                'stored_game' => new PlayableDefinition('playable_game'),
            ]);
        }
        return $articles;
    }

    public function findReviews(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppArray($article, $this->def, 'article_', childrenToProcess: [
                'stored_playable' => new PlayableDefinition('playable'),
                'stored_review' => new ReviewDefinition(),
            ]);
        }
        return $articles;
    }

    public function getDefinition(): ModelDefinition {
        return $this->def;
    }

    public function updateArticle(string $previousId, AppObject $appObject, bool $updateCoverFilename = true): void {
        $dbArray = $this->em->toDbArray($appObject, $this->def);
        if ($previousId === $dbArray['id']) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET article_category_id = ?, article_body = ?, article_is_featured = ?, article_title = ?, ' . ($updateCoverFilename ? 'article_cover_filename = ?, ' : '') . 'article_last_update_date_time = NOW() WHERE article_id = ?;');
            $parameters = [
                $dbArray['category_id'],
                $dbArray['body'],
                $dbArray['is_featured'],
                $dbArray['title'],
                $dbArray['cover_filename'],
                $dbArray['id'],
            ];

            $stmt->execute($parameters);
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->addNewArticle($appObject);
            $this->deleteArticle($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}