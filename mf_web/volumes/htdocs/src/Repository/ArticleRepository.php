<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Searchable;
use LM\WebFramework\DataStructures\SearchQuery;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\SearchEngine\SearchEngine;
use MF\Model\AuthorModelFactory;
use MF\Model\CategoryModelFactory;
use MF\Model\ContributionModelFactory;
use MF\Model\PlayableLinkModelFactory;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ArticleModelFactory;
use MF\Model\ChapterIndexModelFactory;
use MF\Model\PlayableModelFactory;
use MF\Model\ReviewModelFactory;
use OutOfBoundsException;

class ArticleRepository implements IRepository
{
    public function __construct(
        private ArticleModelFactory $articleModelFactory,
        private AuthorModelFactory $authorModelFactory,
        private CategoryModelFactory $categoryModelFactory,
        private ChapterIndexModelFactory $chapterIndexModelFactory,
        private ContributionModelFactory $contributionModelFactory,
        private DatabaseManager $conn,
        private DbEntityManager $em,
        private PlayableLinkModelFactory $playableLinkModelFactory,
        private PlayableModelFactory $playableModelFactory,
        private ReviewModelFactory $reviewModelFactory,
        private SearchEngine $searchEngine,
        private SessionManager $session,
    ) {
    }

    public function add(AppObject $appObject): string {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_article SET article_id = :id, article_author_id = :author_id, article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_sub_title = :sub_title, article_title = :title, article_cover_filename = :cover_filename, article_thumbnail_filename = :thumbnail_filename;');
        $stmt->execute($dbArray);
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
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

        $reviewModel = $this->reviewModelFactory->create(
           $this->playableModelFactory->create(
                gameModel: $this->playableModelFactory->create(isNullable: true),
                contributionModel: $fetchPlayableContributors ? $this->contributionModelFactory->create($this->authorModelFactory->create()) : null,
                playableLinkModel: $this->playableLinkModelFactory->create(),
            ),
            isNullable: true,
        );

        $articleModel = $this->articleModelFactory->create(
            authorModel: $this->authorModelFactory->create(),
            categoryModel: $this->categoryModelFactory->create(),
            reviewModel: $reviewModel,
            chapterIndexModel: $this->chapterIndexModelFactory->create(isNullable: true),
        );

        return $this->em->convertDbRowsToAppObject($data, $articleModel);
    }

    public function findAll(bool $onlyPublished = true): array {
        $selectFrom = $onlyPublished ? 'v_article_published' : 'v_article';

        $results = $this->conn->getPdo()->query("SELECT * FROM {$selectFrom} ORDER BY article_creation_date_time DESC;")->fetchAll();

        $articles = [];
        foreach ($results as $rowNumber => $r) {

            $model = $this->articleModelFactory->create(
                categoryModel: $this->categoryModelFactory->create(),
                reviewModel: $this->reviewModelFactory->create(
                    $this->playableModelFactory->create($this->playableModelFactory->create(isNullable: true), isNullable: true),
                    isNullable: true,
                ),
            );
            $articles[] = $this->em->convertDbRowsToAppObject($results, $model, $rowNumber);
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

        $model = $this->articleModelFactory->create(
            categoryModel: $this->categoryModelFactory->create(),
            reviewModel: $this->reviewModelFactory->create($this->playableModelFactory->create($this->playableModelFactory->create(isNullable: true)), isNullable: true)
        );
        foreach ($results as $rowNumber => $r) {
            $articles[] = $this->em->convertDbRowsToAppObject($results, $model, $rowNumber);
        }
        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findAllReviews(bool $onlyPublished = true): array
    {
        $wherePublished = $onlyPublished ? 'AND article_is_published = 1' : '';
        $results = $this->conn->fetchRows("SELECT * FROM v_article WHERE review_id IS NOT NULL $wherePublished;");

        $model = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create(), reviewModel: $this->reviewModelFactory->create($this->playableModelFactory->create($this->playableModelFactory->create(isNullable: true))));
        $articles = $this->em->convertDbList($results, new ListModel($model));

        return $articles;
    }

    /**
     * @todo Create and use fetchRows method that takes the filename of a SQL query?
     * @return AppObject[]
     */
    public function findArticlesFrom(string $memberId): array
    {
        $articleRows = $this->conn->fetchRows('SELECT * FROM v_article WHERE article_is_published = 1 AND article_author_id = ? ORDER BY article_last_update_date_time DESC;', [$memberId]);

        $model = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create());
        $articles = $this->em->convertDbList($articleRows, new ListModel($model));

        return $articles;
    }

    public function findAvailableArticles(): array
    {
        $articleRows = $this->conn->fetchRows('SELECT * FROM v_article WHERE review_id IS NULL;');
        $model = $this->articleModelFactory->create();
        return $this->em->convertDbList($articleRows, new ListModel($model));
    }

    public function findFeatured(): array
    {
        $articleRows = $this->conn->fetchRows('SELECT * FROM v_article WHERE article_is_featured = 1 AND article_is_published = 1 ORDER BY article_last_update_date_time DESC;');

        return $this->em->convertDbList($articleRows, new ListModel($this->articleModelFactory->create()));
    }

    /**
     * @return AppObject[]
     */
    public function findLastArticles(int $limit = 8, bool $onlyReviews = false): array
    {
        $whereClause = $onlyReviews ? 'AND article_review_id IS NOT NULL' : '';
        $articleRows = $this->conn->fetchRows("SELECT * FROM v_article WHERE article_is_published = 1 {$whereClause} ORDER BY article_creation_date_time DESC LIMIT {$limit};");
        $model = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create());
        $articles = $this->em->convertDbList($articleRows, new ListModel($model));

        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findLastReviews(): array
    {
        $articleRows = $this->conn->fetchRows("SELECT * FROM v_article_published WHERE review_id IS NOT NULL ORDER BY article_creation_date_time DESC LIMIT 4;");

        $model = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create(), reviewModel: $this->reviewModelFactory->create(playableModel: $this->playableModelFactory->create(gameModel: $this->playableModelFactory->create(isNullable: true))));

        return $this->em->convertDbList($articleRows, new ListModel($model));
    }

    public function findOne(string $id): AppObject
    {
        $article = $this->find($id);
        if (null === $article) {
            throw new OutOfBoundsException();
        }
        return $article;
    }

    /**
     * @return AppObject[]
     */
    public function findRelatedArticles(AppObject $article): array
    {
        $articleRows = $this->conn->fetchRows('SELECT * FROM e_article WHERE article_is_published = 1 AND article_category_id = :category_id AND article_id != :id;', ['category_id' => $article->category_id, 'id' => $article->id]);

        return $this->em->convertDbList($articleRows, new ListModel($this->articleModelFactory->create()));
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

        $dbRows = $this->conn->fetchRows($sqlQuery, $parameters);
        $searchables = [
            new Searchable('article_title', 1),
            new Searchable('article_sub_title', .95),
            new Searchable('article_body', .7),
            new Searchable('playable_name', 0.8),
            new Searchable('review_body', 0.8),
            new Searchable('review_cons', .7),
            new Searchable('review_pros', .7),
        ];
        $articleReviewModel = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create(), reviewModel: $this->reviewModelFactory->create($this->playableModelFactory->create()));
        $articleModel = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create());
        $results = [];
        foreach ($dbRows as $rowNo => $row) {
            $ranking = $this->searchEngine->rankResult($searchQuery, $row, $searchables);
            if ($ranking >= $minRanking) {
                $a = $this->em->convertDbRowsToAppObject($dbRows, null !== $row['review_id'] ? $articleReviewModel : $articleModel, $rowNo);
                $results[] = $a->set('ranking', $ranking);
            }
        }
        usort($results, fn ($a, $b) => $b->ranking - $a->ranking);
        return $results;
    }

    public function update(AppObject $appObject, ?string $previousId = null, bool $updateAuthor = false): void {
        $dbArray = $this->em->toDbValue($appObject);

        $this->conn->getPdo()->beginTransaction();

        $stmt = $this->conn->getPdo()->prepare('UPDATE e_article SET article_id = :id, ' . ($updateAuthor ? 'article_author_id = :author_id, ' : '') . 'article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_title = :title, article_sub_title = :sub_title, article_cover_filename = :cover_filename, article_last_update_date_time = NOW(), article_thumbnail_filename = :thumbnail_filename WHERE article_id = :old_id;');
        
        if (!$updateAuthor) {
            unset($dbArray['author_id']);
        }
        $chapterId = $dbArray['chapter_id'];
        unset($dbArray['chapter_id']);
        $dbArray['old_id'] = $previousId ?? $dbArray['id'];

        $stmt->execute($dbArray);

        $previousChapterIndex = $this->conn->fetchNullableRow(
            'SELECT * FROM e_chapter_index WHERE chapter_index_article_id = :previous_id;',
            [
                'previous_id' => $previousId,
            ]
        );
        if (null === $chapterId && null !== $previousChapterIndex) {
            $stmt = $this->conn->run(
                'DELETE FROM e_chapter_index WHERE chapter_index_article_id = :article_id;',
                [
                    'article_id' => $previousId
                ]
            );
        }
        elseif (null !== $chapterId) {
            $highestChapterOrder = $this->conn->fetchNullableRow(
                'SELECT * FROM e_chapter_index WHERE chapter_index_chapter_id = :chapter_id;',
                [
                    'chapter_id' => $chapterId,
                ]
            );
            if (null == $previousChapterIndex) {
                $this->conn->run(
                    'INSERT INTO e_chapter_index SET chapter_index_article_id = :id, chapter_index_chapter_id = :chapter_id, chapter_index_order = :order;',
                    [
                        'id' => $dbArray['id'],
                        'chapter_id' => $chapterId,
                        'order' => $highestChapterOrder ?? 0,
                    ]
                );
            }
            else {
                $this->conn->run(
                    'UPDATE e_chapter_index SET chapter_index_article_id = :id, chapter_index_chapter_id = :chapter_id, chapter_index_order = :order WHERE chapter_index_article_id = :previous_id;',
                    [
                        'id' => $dbArray['id'],
                        'chapter_id' => $chapterId,
                        'previous_id' => $previousId,
                        'order' => $highestChapterOrder ?? 0,
                    ]
                );
            }
        }

        $this->conn->getPdo()->commit();
    }
}