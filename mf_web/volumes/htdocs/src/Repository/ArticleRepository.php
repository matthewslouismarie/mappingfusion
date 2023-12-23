<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructures\AppObject;
use MF\Model\AuthorModel;
use MF\Model\CategoryModel;
use MF\Model\ContributionModel;
use MF\Model\MemberModel;
use MF\Model\PlayableLinkModel;
use MF\Session\SessionManager;
use MF\Model\ArticleModel;
use MF\Model\PlayableModel;
use MF\Model\ReviewModel;
use OutOfBoundsException;

class ArticleRepository implements IRepository
{
    public function __construct(
        private ArticleModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private SessionManager $session,
    ) {
        $this->model = new ArticleModel(
            categoryModel: new CategoryModel(),
        );
    }

    public function add(AppObject $appObject): void {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article SET article_id = :id, article_author_id = :author_id, article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_sub_title = :sub_title, article_title = :title, article_cover_filename = :cover_filename, article_thumbnail_filename = :thumbnail_filename;');
        $stmt->execute($dbArray);
    }

    public function deleteArticle(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_article WHERE article_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id, bool $fetchPlayableContributors = false, bool $onlyPublished = true): ?AppObject {
        $wherePublished = $onlyPublished ? 'AND article_is_published = 1' : '';

        $stmt = $this->conn->getPdo()->prepare("SELECT v_article.*, v_playable.*, v_person.author_id AS redactor_id, v_person.author_name AS redactor_name FROM v_article LEFT OUTER JOIN v_playable ON v_article.playable_id = v_playable.playable_id LEFT JOIN v_person ON v_article.article_author_id = v_person.member_id WHERE article_id = ? $wherePublished;");
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();

        if (0 === count($data)) {
            return null;
        }

        $contribs = [];
        $links = [];
        $reviewModel = null;

        if (null !== $data[0]['review_id']) {
            $reviewModel = new ReviewModel(new PlayableModel(
                gameModel: new PlayableModel(),
                contributionModel: $fetchPlayableContributors ? new ContributionModel(new AuthorModel()) : null,
                playableLinkModel: new PlayableLinkModel(),
            ));
        }

        $contribIds = [];
        $linkIds = [];
        foreach ($data as $row) {
            if (null !== $row['link_id'] && !in_array($row['link_id'], $linkIds, true)) {
                $linkIds[] = $row['link_id'];
                $links[] = $row;
            }
            if (null !== $row['contribution_id'] && !in_array($row['contribution_id'], $contribIds, true)) {
                $contribIds[] = $row['contribution_id'];
                $contribs[] = $row;
            }
        }

        $data[0]['links'] = $links;
        $data[0]['contributions'] = $contribs;

        $articleModel = new ArticleModel(
            authorModel: new AuthorModel(),
            categoryModel: new CategoryModel(),
            reviewModel: $reviewModel,
        );
        return $this->em->toAppData($data[0], $articleModel, 'article');
    }

    public function findAll(bool $onlyPublished = true): array {
        $wherePublished = $onlyPublished ? 'WHERE article_is_published = 1' : '';

        $results = $this->conn->getPdo()->query("SELECT * FROM v_article {$wherePublished};")->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, new ArticleModel(categoryModel: new CategoryModel()), 'article');
        }
        return $entities;
    }

    public function findAllNonReviews(): array {
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE review_id IS NULL AND article_is_published = 1 ORDER BY article_creation_date_time;")->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, new ArticleModel(categoryModel: new CategoryModel()), 'article');
        }
        return $entities;
    }

    /**
     * @return AppObject[]
     */
    public function findAllReviews(bool $onlyPublished = true): array {
        $wherePublished = $onlyPublished ? 'AND article_is_published = 1' : '';
        $results = $this->conn->getPdo()->query("SELECT *, playable_game_id AS game_id, playable_game_name AS game_name, playable_game_release_date_time AS game_release_date_time, NULL AS game_game_id FROM v_article WHERE review_id IS NOT NULL $wherePublished;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, new ArticleModel(categoryModel: new CategoryModel(), reviewModel: new ReviewModel(new PlayableModel(new PlayableModel()))), 'article');
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findArticlesFrom(string $memberId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_article WHERE article_is_published = 1 AND article_author_id = ?;');
        $stmt->execute([$memberId]);
        $articles = [];
        foreach ($stmt->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, new ArticleModel(categoryModel: new CategoryModel()), 'article');
        }
        return $articles;
    }

    public function findAvailableArticles(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE review_id IS NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, new ArticleModel(), 'article');
        }
        return $entities;
    }

    public function findFeatured(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM v_article WHERE article_is_featured = 1 AND article_is_published = 1 ORDER BY article_last_update_date_time DESC;');
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, $this->model, 'article');
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findLastArticles(int $limit = 8, bool $onlyReviews = false): array {
        $whereClause = $onlyReviews ? 'AND article_review_id IS NOT NULL' : '';
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE article_is_published = 1 {$whereClause} ORDER BY article_creation_date_time DESC LIMIT {$limit};");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, new ArticleModel(categoryModel: new CategoryModel()), 'article');
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findLastReviews(): array {
        $results = $this->conn->getPdo()->query("SELECT *, playable_game_id AS game_id, playable_game_name AS game_name, playable_game_release_date_time AS game_release_date_time, NULL AS game_game_id FROM v_article WHERE article_is_published = 1 AND review_id IS NOT NULL ORDER BY article_creation_date_time DESC LIMIT 4;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, new ArticleModel(categoryModel: new CategoryModel(), reviewModel: new ReviewModel(new PlayableModel(new PlayableModel()))), 'article');
        }
        return $articles;
    }

    public function findOne(string $id): AppObject {
        $article = $this->find($id);
        if (null === $article) {
            throw new OutOfBoundsException();
        }
        return $article;
    }

    /**
     * @return AppObject[]
     */
    public function findRelatedArticles(AppObject $article): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_article WHERE article_is_published = 1 AND article_category_id = :category_id AND article_id != :id;');
        $stmt->execute(['category_id' => $article->category_id, 'id' => $article->id]);
        $relatedArticles = [];
        foreach ($stmt->fetchAll() as $row) {
            $relatedArticles[] = $this->em->toAppData($row, new ArticleModel(), 'article');
        }
        return $relatedArticles;
    }

    public function updateArticle(AppObject $appObject, ?string $previousId = null, bool $updateAuthor = false): void {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET article_id = :id, ' . ($updateAuthor ? 'article_author_id = :author_id, ' : '') . 'article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_title = :title, article_sub_title = :sub_title, article_cover_filename = :cover_filename, article_last_update_date_time = NOW(), article_thumbnail_filename = :thumbnail_filename WHERE article_id = :old_id;');
        
        if (!$updateAuthor) {
            unset($dbArray['author_id']);
        }
        $dbArray['old_id'] = $previousId ?? $dbArray['id'];

        $stmt->execute($dbArray);
    }
}