<?php

namespace MF\Test;

use DateTimeImmutable;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructures\AppObject;
use MF\Framework\Test\IUnitTest;
use MF\Model\ArticleModel;
use MF\Model\CategoryModel;
use MF\Model\PlayableModel;
use MF\Model\ReviewModel;

class DbEntityManagerTest implements IUnitTest
{
    public function __construct(
        private DbEntityManager $dbEntityManager,
    ) {
    }

    public function run(): array {
        $tester = new Tester();
        $appObject = $this->dbEntityManager->toAppData(
            [
                'article_id' => 'prout-prite',
                'article_author_id' => 'root',
                'article_category_id' => 'cat',
                'article_body' => 'Ceci est un contenu.',
                'article_is_featured' => 0,
                'article_is_published' => 0,
                'article_sub_title' => 'Un sous-titre',
                'article_title' => 'prout-Prite',
                'article_cover_filename' => 'invwalls.jpg',
                'article_creation_date_time' => '2023-08-12 22:54:59',
                'article_last_update_date_time' => '2023-08-12 23:10:16',
                'category_id' => 'cat',
                'category_name' => 'Une catégorie',
                'playable_id' => NULL,
                'playable_name' => NULL,
                'playable_release_date_time' => NULL,
                'playable_game_id' => NULL,
                'review_id' => NULL,
                'review_article_id' => NULL,
                'review_playable_id' => NULL,
                'review_rating' => NULL,
                'review_body' => NULL,
                'review_cons' => NULL,
                'review_pros' => NULL,
                'playable_game_name' => NULL,
                'playable_game_release_date_time' => NULL,
                'playable_game_game_id' => NULL,
            ],
            new ArticleModel(new CategoryModel())
        , 'article');

        $expectedAppObject = new AppObject([
            'author_id' => 'root',
            'body' => 'Ceci est un contenu.',
            'category_id' => 'cat',
            'cover_filename' => 'invwalls.jpg',
            'creation_date_time' => new DateTimeImmutable('2023-08-12 22:54:59'),
            'id' => 'prout-prite',
            'is_featured' => false,
            'is_published' => false,
            'last_update_date_time' => new DateTimeImmutable('2023-08-12 23:10:16'),
            'sub_title' => 'Un sous-titre',
            'title' => 'prout-Prite',
            'category' => new AppObject([
                'id' => 'cat',
                'name' => 'Une catégorie',
            ]),
        ]);

        $tester->assertArrayEquals(
            $expectedAppObject->toArray(),
            $appObject->toArray(),
        );

        $tester->assertTrue(
            $expectedAppObject->isEqualTo($appObject)
        );

        return $tester->getErrors();
    }
}