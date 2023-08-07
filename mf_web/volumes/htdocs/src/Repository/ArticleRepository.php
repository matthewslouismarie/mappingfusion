<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObject;
use MF\Session\SessionManager;
use MF\Model\ArticleModel;
use MF\Model\PlayableModel;
use MF\Model\ReviewModel;
use OutOfBoundsException;
use UnexpectedValueException;

class ArticleRepository implements IRepository
{
    const GROUPS = ['category', 'review', ['playable', ['review', 'playable']]];

    public function __construct(
        private ArticleModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private SessionManager $session,
    ) {
        $this->model = new ArticleModel(new ReviewModel());
    }

    public function add(array $articleScalarArray): void {
        $dbArray = $this->em->toDbValue($articleScalarArray);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article VALUES (:id, :author_id, :category_id, :body, :is_featured, :sub_title, :title, :cover_filename, :creation_date_time, :last_update_date_time);');
        $stmt->execute($dbArray);
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
                'stored_review' => new ReviewModel(),
                'stored_playable' => new PlayableModel(),
            ];
            return new AppObject($this->em->toScalarArray(
                $data[0],
                'article',
                self::GROUPS,
            ), $this->model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAvailableArticles(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = new AppObject($this->em->toScalarArray($r, 'article', self::GROUPS), $this->model);
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
            $entities[] = new AppObject($this->em->toScalarArray($r, 'article', self::GROUPS), $this->model);
        }
        return $entities;
    }

    public function findFeatured(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE article_is_featured = 1;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = new AppObject($this->em->toScalarArray($article, 'article', self::GROUPS), $this->model);
        }
        return $articles;
    }

    public function findLast(int $limit = 8, bool $onlyReviews = false): array {
        $whereClause = $onlyReviews ? 'WHERE article_review_id IS NOT NULL' : '';
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article {$whereClause} ORDER BY article_last_update_date_time DESC LIMIT {$limit};");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = new AppObject($this->em->toScalarArray($article, 'article', self::GROUPS), $this->model);
        }
        return $articles;
    }

    public function findLastReviews(): array {
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE review_id IS NOT NULL ORDER BY article_last_update_date_time DESC LIMIT 4;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = new AppObject($this->em->toScalarArray($article, 'article', self::GROUPS), $this->model);
        }
        return $articles;
    }

    public function findReviews(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NOT NULL;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = new AppObject($this->em->toScalarArray($article, 'article', self::GROUPS), $this->model);
        }
        return $articles;
    }

    public function updateArticle(string $previousId, array $appArray, bool $updateCoverFilename = true): void {
        $dbArray = $this->em->toDbValue($appArray);
        if ($previousId === $dbArray['id']) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET article_category_id = ?, article_body = ?, article_is_featured = ?, article_title = ?, artiicle_sub_title = ?, ' . ($updateCoverFilename ? 'article_cover_filename = ?, ' : '') . 'article_last_update_date_time = NOW() WHERE article_id = ?;');
            $parameters = [
                $dbArray['category_id'],
                $dbArray['body'],
                $dbArray['is_featured'],
                $dbArray['title'],
                $dbArray['sub_title'],
                $dbArray['cover_filename'],
                $dbArray['id'],
            ];

            $stmt->execute($parameters);
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($appArray);
            $this->deleteArticle($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}