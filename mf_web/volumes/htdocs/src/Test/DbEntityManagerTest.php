<?php

namespace MF\Test;

use DateTimeImmutable;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\Database\Exceptions\InvalidDbDataException;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use LM\WebFramework\Test\IUnitTest;
use MF\Model\ArticleModel;
use MF\Model\BookModel;
use MF\Model\CategoryModel;
use MF\Model\ChapterModel;

class DbEntityManagerTest implements IUnitTest
{
    public function __construct(
        private DbEntityManager $dbEntityManager,
    ) {
    }

    public function run(): array {
        $bookModel = new BookModel();
        $em = $this->dbEntityManager;
        $tester = new Tester();

        $expectedBook = new AppObject([
            'id' => 'another-book',
            'title' => 'Mon deuxième livre…',
        ]);


        $appObject = $em->toAppData(
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
                'article_thumbnail_filename' => null,
                'article_creation_date_time' => '2023-08-12 22:54:59',
                'article_last_update_date_time' => '2023-08-12 23:10:16',
                'category_id' => 'cat',
                'category_name' => 'Une catégorie',
                'category_parent_id' => null,
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
            new ArticleModel(categoryModel: new CategoryModel())
        , 'article');

        $expectedAppObject = new AppObject([
            'author_id' => 'root',
            'body' => 'Ceci est un contenu.',
            'category_id' => 'cat',
            'cover_filename' => 'invwalls.jpg',
            'thumbnail_filename' => null,
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
                'parent_id' => null,
            ]),
        ]);

        $tester->assertArrayEquals(
            $expectedAppObject->toArray(),
            $appObject->toArray(),
        );

        $tester->assertTrue(
            $expectedAppObject->isEqualTo($appObject)
        );


        $dbList = $em->toAppData([
            [
              'book_id' => 'another-book',
              'book_title' => 'Mon deuxième livre…',
            ],
            [
              'book_id' => 'a-book',
              'book_title' => 'Mon premier livre !',
            ],
        ], new ListModel(new BookModel()), 'book');

        $appObjectList = new AppObject([
            new AppObject([
                'id' => 'another-book',
                'title' => 'Mon deuxième livre…',
            ]),
            new AppObject([
                'id' => 'a-book',
                'title' => 'Mon premier livre !',
            ])
        ]);

        

        $tester->assertArrayEquals(
            $appObjectList->toArray(),
            $dbList->toArray(),
        );

        $tester->assertTrue(
            $appObjectList->isEqualTo($dbList)
        );

        $nullDbData = [
            'book_id' => null,
            'book_title' => null,
        ];

        $nullableBookModel = new class extends BookModel {
            public function isNullable(): bool
            {
                return true;
            }
        };
        $tester->assertNull($em->toAppData($nullDbData, $nullableBookModel, 'book'));

        $notSoNullDbData = [
            'book_id' => 'hi',
            'book_title' => null,
        ];

        $tester->assertException(InvalidDbDataException::class, function () use ($em, $notSoNullDbData, $nullableBookModel) {
            $em->toAppData($notSoNullDbData, $nullableBookModel, 'book');
        });

        /**
         * Check extra properties are ignored.
         */

        $unPetitTrucEnPlus = [
            'book_id' => 'another-book',
            'book_title' => 'Mon deuxième livre…',
            'book_prout' => 'Oui j’ai un truc en plus, et alors ?',
            'megawesh' => null,
            'yep' => 43,
            'walah' => 'mais ça fait plus d’un truc en plus là…',
        ];
        $tester->assertArrayEquals(
            $expectedBook->toArray(),
            $em->toAppData($unPetitTrucEnPlus, $bookModel, 'book')->toArray(),
        );

        $dbNestedModel = new BookModel(new ChapterModel(new AbstractEntity([
            'id' => new SlugModel(),
            'title' => new StringModel(),
        ])));

        $dbDataOfBookWithChapters = [
          [
            'book_id' => 'a-book',
            'book_title' => 'Mon premier livre !',
            'chapter_id' => 'chapter-1',
            'chapter_book_id' => 'a-book',
            'chapter_title' => 'Au commencement…',
            'article_id' => NULL,
            'article_chapter_id' => NULL,
            'article_title' => NULL,
          ],
          [
            'book_id' => 'a-book',
            'book_title' => 'Mon premier livre !',
            'chapter_id' => 'chapter-2',
            'chapter_book_id' => 'a-book',
            'chapter_title' => 'Puis ensuite',
            'article_id' => NULL,
            'article_chapter_id' => NULL,
            'article_title' => NULL,
          ],
          [
            'book_id' => 'a-book',
            'book_title' => 'Mon premier livre !',
            'chapter_id' => 'chapter-3',
            'chapter_book_id' => 'a-book',
            'chapter_title' => 'Conclusion…',
            'article_id' => NULL,
            'article_chapter_id' => NULL,
            'article_title' => NULL,
          ],
        ];
        $expectedBookWithChapters = new AppObject([
            'id' => 'a-book',
            'title' => 'Mon premier livre !',
            'chapters' => [
                [
                    'id' => 'chapter-1',
                    'book_id' => 'a-book',
                    'title' => 'Au commencement…',
                    'articles' => [],
                ],
                [
                    'id' => 'chapter-2',
                    'book_id' => 'a-book',
                    'title' => 'Puis ensuite',
                    'articles' => [],
                ],
                [
                    'id' => 'chapter-3',
                    'book_id' => 'a-book',
                    'title' => 'Conclusion…',
                    'articles' => [],
                ],
            ]
        ]);

        $tester->assertArrayEquals(
            $expectedBookWithChapters->toArray(),
            $em->toAppData($dbDataOfBookWithChapters, $dbNestedModel, 'book')->toArray(),
        );

        // Book with chapters with articles
        $doubleNestedDbData = [
            [
              'book_id' => 'a-book',
              'book_title' => 'Mon premier livre !',
              'chapter_id' => 'chapter-1',
              'chapter_book_id' => 'a-book',
              'chapter_title' => 'Au commencement…',
              'article_id' => 'mon-premier-article',
              'article_chapter_id' => 'chapter-1',
              'article_title' => 'Mon premier article',
            ],
            [
              'book_id' => 'a-book',
              'book_title' => 'Mon premier livre !',
              'chapter_id' => 'chapter-1',
              'chapter_book_id' => 'a-book',
              'chapter_title' => 'Au commencement…',
              'article_id' => 'mon-deuxieme-article',
              'article_chapter_id' => 'chapter-1',
              'article_title' => 'Mon deuxieme article',
            ],
            [
              'book_id' => 'a-book',
              'book_title' => 'Mon premier livre !',
              'chapter_id' => 'chapter-2',
              'chapter_book_id' => 'a-book',
              'chapter_title' => 'Puis ensuite',
              'article_id' => NULL,
              'article_chapter_id' => NULL,
              'article_title' => NULL,
            ],
            [
              'book_id' => 'a-book',
              'book_title' => 'Mon premier livre !',
              'chapter_id' => 'chapter-3',
              'chapter_book_id' => 'a-book',
              'chapter_title' => 'Conclusion…',
              'article_id' => NULL,
              'article_chapter_id' => NULL,
              'article_title' => NULL,
            ],
          ];
          $doubleNestedExpected = new AppObject([
              'id' => 'a-book',
              'title' => 'Mon premier livre !',
              'chapters' => [
                  [
                      'id' => 'chapter-1',
                      'book_id' => 'a-book',
                      'title' => 'Au commencement…',
                      'articles' => [
                        [
                            'id' => 'mon-premier-article',
                            'title' => 'Mon premier article',
                        ],
                        [
                            'id' => 'mon-deuxieme-article',
                            'title' => 'Mon deuxieme article',
                        ],
                      ],
                  ],
                  [
                      'id' => 'chapter-2',
                      'book_id' => 'a-book',
                      'title' => 'Puis ensuite',
                      'articles' => [],
                  ],
                  [
                      'id' => 'chapter-3',
                      'book_id' => 'a-book',
                      'title' => 'Conclusion…',
                      'articles' => [],
                  ],
              ]
          ]);

          $tester->assertArrayEquals(
              $doubleNestedExpected->toArray(),
              $em->toAppData($doubleNestedDbData, $dbNestedModel, 'book')->toArray(),
              'The double-nested book was not recontrusted properly.'
          );

        return $tester->getErrors();
    }
}