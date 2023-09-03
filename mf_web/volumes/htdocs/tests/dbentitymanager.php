<?php

use MF\Database\DbEntityManager;
use MF\Model\ArticleModel;
use MF\Model\CategoryModel;
use MF\Model\PlayableModel;
use MF\Model\ReviewModel;
use MF\Test\Tester;

$container = require_once(dirname(__FILE__) . '/../index.php');

$tester = $container->get(Tester::class);

$em = $container->get(DbEntityManager::class);

$appObject = $em->toAppData(
    [
        'article_id' => 'prout-prite',
        'article_author_id' => 'root',
        'article_category_id' => 'cat',
        'article_body' => 'Ceci est un contenu.',
        'article_is_featured' => 0,
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
    new ArticleModel(new CategoryModel(), new ReviewModel(new PlayableModel()))
);

$tester->assertArrayEquals(
    [
        'author_id' => 'root',
        'body' => 'Ceci est un contenu.',
        'category_id' => 'cat',
        'cover_filename' => 'invwalls.jpg',
        'creation_date_time' => new DateTimeImmutable('2023-08-12 22:54:59'),
        'id' => 'prout-prite',
        'is_featured' => false,
        'last_update_date_time' => new DateTimeImmutable('2023-08-12 23:10:16'),
        'sub_title' => 'Un sous-titre',
        'title' => 'prout-Prite',
        'category' => [
            'id' => 'cat',
            'name' => 'Une catégorie',
        ],
        'review' => null,
    ],
    $appObject->toArray(),
);

// $tester->assertEquals(
//     [
//         'name' => 'Georges',
//     ],
//     $em->getScalarProperty('author_name', 'Georges', [['author', []]]),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//         'review' => [
//             'playable' => [
//                 'name' => 'Half-Life',
//                 'year' => 1998,
//             ],
//             'rating' => 5,
//         ],
//         'admin' => true,
//     ],
//     $em->toAppData([
//         'author_name' => 'M. Grinchon',
//         'playable_name' => 'Half-Life',
//         'playable_year' => 1998,
//         'review_rating' => 5,
//         'admin' => true,
//     ], 'author', ['review', ['playable', ['review', 'playable']]]),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//     ],
//     $em->toAppData([
//         'author_name' => 'M. Grinchon',
//     ], 'author'),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//         'category' => [
//             'name' => 'yo',
//         ],
//         'tag' => [
//             'name' => 'hi',
//         ],
//     ],
//     $em->toAppData([
//         'name' => 'M. Grinchon',
//         'category_name' => 'yo',
//         'tag_name' => 'hi',
//     ], groups: ['category', 'tag']),
// );
