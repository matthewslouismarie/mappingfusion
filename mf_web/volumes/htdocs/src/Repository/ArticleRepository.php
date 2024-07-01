<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Searchable;
use LM\WebFramework\DataStructures\SearchQuery;
use LM\WebFramework\SearchEngine\SearchEngine;
use MF\Model\AuthorModel;
use MF\Model\CategoryModel;
use MF\Model\ContributionModel;
use MF\Model\PlayableLinkModel;
use LM\WebFramework\Session\SessionManager;
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
        private SearchEngine $searchEngine,
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
        $selectFrom = $onlyPublished ? 'v_article_published' : 'v_article';

        $stmt = $this->conn->getPdo()->prepare("SELECT a.*, v_playable.*, v_person.author_id AS redactor_id, v_person.author_name AS redactor_name, v_person.author_avatar_filename AS redactor_avatar_filename FROM {$selectFrom} AS a LEFT OUTER JOIN v_playable ON a.playable_id = v_playable.playable_id LEFT JOIN v_person ON a.article_author_id = v_person.member_id WHERE article_id = ?;");
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();

        if (0 === count($data)) {
            return null;
        }

        $contribs = [];
        $links = [];

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

        $reviewModel = new ReviewModel(
            new PlayableModel(
                gameModel: new PlayableModel(isNullable: true),
                contributionModel: $fetchPlayableContributors ? new ContributionModel(new AuthorModel()) : null,
                playableLinkModel: new PlayableLinkModel(),
            ),
            isNullable: true,
        );

        $articleModel = new ArticleModel(
            authorModel: new AuthorModel(),
            categoryModel: new CategoryModel(),
            reviewModel: $reviewModel,
        );

        return $this->em->toAppData($data[0], $articleModel, 'article');
    }

    public function findAll(bool $onlyPublished = true): array {
        $selectFrom = $onlyPublished ? 'v_article_published' : 'v_article';

        $results = $this->conn->getPdo()->query("SELECT * FROM {$selectFrom} ORDER BY article_creation_date_time DESC;")->fetchAll();

        $articles = [];
        foreach ($results as $r) {

            $model = new ArticleModel(
                categoryModel: new CategoryModel(),
                reviewModel: new ReviewModel(new PlayableModel(new PlayableModel(isNullable: true), isNullable: true), articleModel: null, isNullable: true)
            );
            $articles[] = $this->em->toAppData($r, $model, 'article');
        }
        return $articles;
    }

    public function findByCategory(string $categoryId): array {
        $stmt = $this->conn->getPdo()->prepare("
            WITH RECURSIVE CategoryHierarchy AS (
                SELECT category_id, category_name, category_parent_id
                FROM e_category AS rootcat
                WHERE rootcat.category_id = ?

                UNION ALL

                SELECT descendantcat.category_id, descendantcat.category_name, descendantcat.category_parent_id
                FROM e_category AS descendantcat
                JOIN CategoryHierarchy ch ON descendantcat.category_parent_id = ch.category_id
            )
            SELECT *
            FROM v_article_published AS a
            JOIN CategoryHierarchy ch ON a.article_category_id = ch.category_id
            ORDER BY article_creation_date_time DESC;
        ");
        $stmt->execute([$categoryId]);
        $results = $stmt->fetchAll();


        $articles = [];

        $model = new ArticleModel(
            categoryModel: new CategoryModel(),
            reviewModel: new ReviewModel(new PlayableModel(new PlayableModel()), articleModel: null, isNullable: true)
        );
        foreach ($results as $r) {
            $articles[] = $this->em->toAppData($r, $model, 'article');
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findAllReviews(bool $onlyPublished = true): array {
        $wherePublished = $onlyPublished ? 'AND article_is_published = 1' : '';
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article WHERE review_id IS NOT NULL $wherePublished;");
        $articles = [];
        $model = new ArticleModel(categoryModel: new CategoryModel(), reviewModel: new ReviewModel(new PlayableModel(new PlayableModel(isNullable: true))));
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, $model, 'article');
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findArticlesFrom(string $memberId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_article WHERE article_is_published = 1 AND article_author_id = ? ORDER BY article_last_update_date_time DESC;');
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
        $results = $this->conn->getPdo()->query("SELECT * FROM v_article_published WHERE review_id IS NOT NULL ORDER BY article_creation_date_time DESC LIMIT 4;");
        $articles = [];
        foreach ($results->fetchAll() as $article) {
            $articles[] = $this->em->toAppData($article, new ArticleModel(categoryModel: new CategoryModel(), reviewModel: new ReviewModel(new PlayableModel(new PlayableModel(isNullable: true)))), 'article');
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

    /**
     * @return array<AppObject>
     */
    public function searchArticles(SearchQuery $searchQuery, float $minRanking = 0.1) {
        $sqlQuery = 'SELECT * FROM v_article WHERE article_is_published = 1 AND (';
        $parameters = [];
        for ($i = 0; $i < count($searchQuery->getKeywords()); $i++) {
            if ($i > 0) {
                $sqlQuery .= " OR";
            }
            $sqlQuery .= " (article_title LIKE :kw{$i}" .
                " OR article_sub_title LIKE :kw{$i}" .
                " OR article_body LIKE :kw{$i}" .
                " OR playable_name LIKE :kw{$i}" .
                " OR review_body LIKE :kw{$i}" .
                " OR review_cons LIKE :kw{$i}" .
                " OR review_pros LIKE :kw{$i}" .
            ")";
            $parameters["kw{$i}"] = "%{$searchQuery->getKeywords()[$i]}%";
        }
        $sqlQuery .= ')';

        $stmt = $this->conn->getPdo()->prepare($sqlQuery);
        $stmt->execute($parameters);
        $searchables = [
            new Searchable('article_title', 1),
            new Searchable('article_sub_title', .95),
            new Searchable('article_body', .7),
            new Searchable('playable_name', 0.8),
            new Searchable('review_body', 0.8),
            new Searchable('review_cons', .7),
            new Searchable('review_pros', .7),
        ];
        $articleReviewModel = new ArticleModel(categoryModel: new CategoryModel(), reviewModel: new ReviewModel(new PlayableModel()));
        $articleModel = new ArticleModel(categoryModel: new CategoryModel());
        $results = [];
        foreach ($stmt->fetchAll() as $row) {
            $ranking = $this->searchEngine->rankResult($searchQuery, $row, $searchables);
            if ($ranking >= $minRanking) {
                $a = $this->em->toAppData($row, null !== $row['review_id'] ? $articleReviewModel : $articleModel, 'article');
                $results[] = $a->set('ranking', $ranking);
            }
        }
        usort($results, fn ($a, $b) => $b->ranking - $a->ranking);
        return $results;
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