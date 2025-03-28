<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppList;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Searchable;
use LM\WebFramework\DataStructures\SearchQuery;
use LM\WebFramework\SearchEngine\SearchEngine;
use MF\Model\AuthorModelFactory;
use MF\Model\CategoryModelFactory;
use MF\Model\ContributionModelFactory;
use MF\Model\PlayableLinkModelFactory;
use LM\WebFramework\Session\SessionManager;
use MF\DataStructure\SqlFilename;
use MF\Model\ArticleModelFactory;
use MF\Model\ChapterIndexModelFactory;
use MF\Model\ModelFactory;
use MF\Model\PlayableModelFactory;
use MF\Model\ReviewModelFactory;
use OutOfBoundsException;

class ArticleRepository implements IUpdatableIdRepository
{
    public function __construct(
        private ArticleModelFactory $articleModelFactory,
        private AuthorModelFactory $authorModelFactory,
        private CategoryModelFactory $categoryModelFactory,
        private CategoryRepository $categoryRepository,
        private ChapterIndexModelFactory $chapterIndexModelFactory,
        private ContributionModelFactory $contributionModelFactory,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private ModelFactory $modelFactory,
        private PlayableLinkModelFactory $playableLinkModelFactory,
        private PlayableModelFactory $playableModelFactory,
        private ReviewModelFactory $reviewModelFactory,
        private SearchEngine $searchEngine,
        private SessionManager $session,
    ) {
    }

    public function add(AppObject $appObject): string
    {
        $this->dbManager->run(
            'INSERT INTO e_article SET article_id = :id, article_author_id = :author_id, article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_sub_title = :sub_title, article_title = :title, article_cover_filename = :cover_filename, article_thumbnail_filename = :thumbnail_filename;',
            $this->em->toDbValue($appObject),
        );

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $stmt = $this->dbManager->run('DELETE FROM e_article WHERE article_id = ?;', [$id]);
    }

    public function find(
        string $id,
        bool $fetchPlayableContributors = false,
        bool $onlyPublished = true,
    ): ?AppObject {
        $tableName = $onlyPublished ? 'v_article_published' : 'v_article';

        $articleDbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('s_find_article.sql'), [$id], $tableName);

        if (0 === count($articleDbRows)) {
            return null;
        }

        $categoryDbRows = $this->dbManager->fetchRows('SELECT * FROM e_category;');

        $dbRows = $this->em->outerJoinDbRows($articleDbRows, $categoryDbRows);

        $articleModel = $this->modelFactory->getArticleModelFull();

        return $this->em->convertDbRowsToAppObject($dbRows, $articleModel);
    }

    public function findAll(bool $onlyPublished = true): AppList
    {
        $selectFrom = $onlyPublished ? 'v_article_published' : 'v_article';

        $dbRows = $this->dbManager->fetchRows("SELECT * FROM {$selectFrom} ORDER BY article_creation_date_time DESC;");
        $articles = $this->em->convertDbRowsToEntityList($dbRows, $this->modelFactory->getArticleModel());
        return $articles;
    }

    public function findByCategory(string $categoryId): AppList
    {
        $results = $this->dbManager->fetchRowsFromQueryFile(
            new SqlFilename('stmt_find_articles_by_category.sql'),
            [$categoryId],
        );

        $articles = [];

        $model = $this->articleModelFactory->create(
            categoryModel: $this->categoryModelFactory->create(),
            reviewModel: $this->reviewModelFactory->create($this->playableModelFactory->create($this->playableModelFactory->create()))
        );
        foreach ($results as $rowNumber => $r) {
            $articles[] = $this->em->convertDbRowsToAppObject($results, $model, $rowNumber);
        }
        return new AppList($articles);
    }

    /**
     * @return AppObject[]
     */
    public function findAllReviews(bool $onlyPublished = true): AppList
    {
        $wherePublished = $onlyPublished ? 'AND article_is_published = 1' : '';
        $results = $this->dbManager->fetchRows("SELECT * FROM v_article WHERE review_id IS NOT NULL $wherePublished;");

        $model = $this->modelFactory->getArticleModel();
        $articles = $this->em->convertDbRowsToEntityList($results, $model);

        return $articles;
    }

    /**
     * @todo   Create and use fetchRows method that takes the filename of a SQL query?
     * @return AppObject[]
     */
    public function findArticlesFrom(string $authorId): AppList
    {
        $articleRows = $this->dbManager->fetchRows('SELECT * FROM v_article WHERE article_is_published = 1 AND article_author_id = ? ORDER BY article_last_update_date_time DESC;', [$authorId]);

        $model = $this->articleModelFactory->create(categoryModel: $this->categoryModelFactory->create());
        $articles = $this->em->convertDbRowsToEntityList($articleRows, $model);

        return $articles;
    }

    public function findArticlesWithNoReview(): AppList
    {
        $articleRows = $this->dbManager->fetchRows('SELECT * FROM v_article WHERE review_id IS NULL;');
        $model = $this->articleModelFactory->create();
        return $this->em->convertDbRowsToEntityList($articleRows, $model);
    }

    public function findFeatured(): AppList
    {
        $articleRows = $this->dbManager->fetchRows('SELECT * FROM v_article WHERE article_is_featured = 1 AND article_is_published = 1 ORDER BY article_last_update_date_time DESC;');

        return $this->em->convertDbRowsToEntityList($articleRows, $this->articleModelFactory->create());
    }

    public function findFreeArticles(): AppList
    {
        $articleRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_free_articles_for_chapter.sql'), []);
        return $this->em->convertDbRowsToEntityList($articleRows, $this->modelFactory->getArticleModel(category: false, chapterIndex: true));
    }

    /**
     * @return AppObject[]
     */
    public function findLastArticles(int $limit = 8, bool $onlyReviews = false): AppList
    {
        $whereClause = $onlyReviews ? 'AND article_review_id IS NOT NULL' : '';
        $articleRows = $this->dbManager->fetchRows("SELECT * FROM v_article WHERE article_is_published = 1 {$whereClause} ORDER BY article_creation_date_time DESC LIMIT {$limit};");
        $model = $this->modelFactory->getArticleModel();
        $articles = $this->em->convertDbRowsToEntityList($articleRows, $model);

        return $articles;
    }

    /**
     * @return AppObject[]
     */
    public function findLastReviews(): AppList
    {
        $articleRows = $this->dbManager->fetchRows("SELECT * FROM v_article_published WHERE review_id IS NOT NULL ORDER BY article_creation_date_time DESC LIMIT 4;");
        $model = $this->modelFactory->getArticleModel(review: true);

        return $this->em->convertDbRowsToEntityList($articleRows, $model);
    }

    public function findOne(string $id): AppObject
    {
        $article = $this->find($id, onlyPublished: false);
        if (null === $article) {
            throw new OutOfBoundsException();
        }
        return $article;
    }

    /**
     * @return AppObject[]
     */
    public function findRelatedArticles(AppObject $article): AppList
    {
        $articleRows = $this->dbManager->fetchRows('SELECT * FROM e_article WHERE article_is_published = 1 AND article_category_id = :category_id AND article_id != :id;', ['category_id' => $article['category_id'], 'id' => $article['id']]);

        return $this->em->convertDbRowsToEntityList($articleRows, $this->articleModelFactory->create());
    }

    /**
     * @return array<AppObject>
     */
    public function searchArticles(SearchQuery $searchQuery, float $minRanking = 0.1)
    {
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

        $dbRows = $this->dbManager->fetchRows($sqlQuery, $parameters);
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
        usort($results, fn ($a, $b) => $b['ranking'] - $a['ranking']);
        return $results;
    }

    public function update(AppObject $appObject, ?string $persistedId = null, bool $updateAuthor = false): void
    {
        if ($appObject->hasProperty('chapter_id')) {
            $appObject = $appObject->removeProperty('chapter_id');
        }
        $dbArray = $this->em->toDbValue($appObject);

        $this->dbManager->getPdo()->beginTransaction();

        $stmt = $this->dbManager->prepare('UPDATE e_article SET article_id = :id, ' . ($updateAuthor ? 'article_author_id = :author_id, ' : '') . 'article_category_id = :category_id, article_body = :body, article_is_featured = :is_featured, article_is_published = :is_published, article_title = :title, article_sub_title = :sub_title, article_cover_filename = :cover_filename, article_last_update_date_time = NOW(), article_thumbnail_filename = :thumbnail_filename WHERE article_id = :persisted_id;');
        
        if (!$updateAuthor) {
            unset($dbArray['author_id']);
        }

        $dbArray['persisted_id'] = $persistedId ?? $dbArray['id'];

        $stmt->execute($dbArray);

        // $previousChapterIndex = $this->dbManager->fetchFirstRow(
        //     'SELECT * FROM e_chapter_index WHERE chapter_index_article_id = :persisted_id;',
        //     [
        //         'persisted_id' => $persistedId,
        //     ],
        // );

        // if (null === $chapterId && null !== $previousChapterIndex) {
        //     $stmt = $this->dbManager->run(
        //         'DELETE FROM e_chapter_index WHERE chapter_index_article_id = :article_id;',
        //         [
        //             'article_id' => $persistedId,
        //         ],
        //     );
        // }
        // elseif (null !== $chapterId) {
        //     $highestChapterOrder = $this->dbManager->fetchFirstRow(
        //         'SELECT * FROM e_chapter_index WHERE chapter_index_chapter_id = :chapter_id;',
        //         [
        //             'chapter_id' => $chapterId,
        //         ]
        //     );
        //     if (null == $previousChapterIndex) {
        //         $this->dbManager->run(
        //             'INSERT INTO e_chapter_index SET chapter_index_article_id = :id, chapter_index_chapter_id = :chapter_id, chapter_index_order = :order;',
        //             [
        //                 'id' => $dbArray['id'],
        //                 'chapter_id' => $chapterId,
        //                 'order' => $highestChapterOrder ?? 0,
        //             ]
        //         );
        //     }
        //     else {
        //         $this->dbManager->run(
        //             'UPDATE e_chapter_index SET chapter_index_article_id = :id, chapter_index_chapter_id = :chapter_id, chapter_index_order = :order WHERE chapter_index_article_id = :persisted_id;',
        //             [
        //                 'id' => $dbArray['id'],
        //                 'chapter_id' => $chapterId,
        //                 'persisted_id' => $persistedId,
        //                 'order' => $highestChapterOrder ?? 0,
        //             ]
        //         );
        //     }
        // }

        $this->dbManager->getPdo()->commit();
    }
}