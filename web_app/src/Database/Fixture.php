<?php

namespace MF\Database;

use DateTimeImmutable;
use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\DataStructures\AppObject;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\BookRepository;
use MF\Repository\CategoryRepository;
use MF\Repository\ChapterRepository;
use MF\Repository\ContributionRepository;
use MF\Repository\AccountRepository;
use MF\Repository\PlayableLinkRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;

class Fixture
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private AuthorRepository $authorRepo,
        private BookRepository $bookRepo,
        private CategoryRepository $catRepo,
        private ChapterRepository $chapterRepo,
        private Configuration $config,
        private ContributionRepository $contribRepo,
        private DatabaseManager $dbManager,
        private AccountRepository $accountRepo,
        private PlayableLinkRepository $playableLinkRepo,
        private PlayableRepository $playableRepo,
        private ReviewRepository $reviewRepo,
    ) {
    }

    public function load(): void
    {
        $this->dbManager->getPdo()->beginTransaction();

        /**
         * Authors
         */


        $loulimi = new AppObject(
            [
            'id' => 'louli',
            'name' => 'Root',
            'avatar_filename' => null,
            ]
        );
        $this->authorRepo->add($loulimi);

        $valve = new AppObject(
            [
            'id' => 'valve',
            'name' => 'Valve',
            'avatar_filename' => null,
            ]
        );
        $this->authorRepo->add($valve);

        $scTeam = new AppObject(
            [
            'id' => 'sven-co-op-team',
            'name' => 'The Sven Co-op Team',
            'avatar_filename' => null,
            ]
        );
        $this->authorRepo->add($scTeam);

        $neophus = new AppObject(
            [
            'id' => 'neophus',
            'name' => 'Neophus',
            'avatar_filename' => null,
            ]
        );
        $this->authorRepo->add($neophus);


        /**
         * Accounts
         */


        $rootAccount = new AppObject(
            [
            'id' => 'root',
            'password' => password_hash($this->config->getSetting('rootAccountPwd'), PASSWORD_DEFAULT),
            'author_id' => 'louli',
            ]
        );
        $this->accountRepo->add($rootAccount);


        /**
         * Playables
         */


        $gs = new AppObject(
            [
            'id' => 'goldsource',
            'name' => 'GoldSource',
            'release_date_time' => new DateTimeImmutable('1998-11-19'),
            'game_id' => null,
            'type' => PlayableType::Standalone->value,
            ]
        );
        $this->playableRepo->add($gs);

        $hl = new AppObject(
            [
            'id' => 'half-life',
            'name' => 'Half-Life',
            'release_date_time' => new DateTimeImmutable('1998-11-19'),
            'game_id' => 'goldsource',
            'type' => PlayableType::Standalone->value,
            ]
        );
        $this->playableRepo->add($hl);

        $hl2 = new AppObject(
            [
            'id' => 'half-life-2',
            'name' => 'Half-Life 2',
            'release_date_time' => new DateTimeImmutable('2004-11-16'),
            'game_id' => null,
            'type' => PlayableType::Standalone->value,
            ]
        );
        $this->playableRepo->add($hl2);

        $sc = new AppObject(
            [
            'id' => 'sven-co-op',
            'name' => 'Sven Co-op',
            'release_date_time' => new DateTimeImmutable('1999-01-19'),
            'game_id' => 'goldsource',
            'type' => PlayableType::Standalone->value,
            ]
        );
        $this->playableRepo->add($sc);

        $cp = new AppObject(
            [
            'id' => 'crossed-paths',
            'name' => 'Crossed Paths',
            'release_date_time' => new DateTimeImmutable('2022-09-07'),
            'game_id' => 'sven-co-op',
            'type' => PlayableType::Map->value,
            ]
        );
        $this->playableRepo->add($cp);


        /**
         * Playable links
         */


        $link1 = new AppObject(
            [
            'id' => null,
            'playable_id' => $sc['id'],
            'name' => 'Homepage',
            'type' => LinkType::HomePage->name,
            'url' => 'https://svencoop.com',
            ]
        );
        $this->playableLinkRepo->add($link1);

        $link2 = new AppObject(
            [
            'id' => null,
            'playable_id' => $sc['id'],
            'name' => 'Download',
            'type' => LinkType::Download->name,
            'url' => 'https://store.steampowered.com/agecheck/app/225840/',
            ]
        );
        $this->playableLinkRepo->add($link2);


        /**
         * Contributions
         */


        $contrib1 = new AppObject(
            [
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
            ]
        );
        $this->contribRepo->add($contrib1);

        $contrib2 = new AppObject(
            [
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
            ]
        );
        $this->contribRepo->add($contrib2);

        $contrib3 = new AppObject(
            [
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl['id'],
            'is_author' => true,
            'summary' => null,
            ]
        );
        $this->contribRepo->add($contrib3);

        $contrib4 = new AppObject(
            [
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl2['id'],
            'is_author' => true,
            'summary' => null,
            ]
        );
        $this->contribRepo->add($contrib4);

        $contrib5 = new AppObject(
            [
            'id' => null,
            'author_id' => $scTeam['id'],
            'playable_id' => $sc['id'],
            'is_author' => true,
            'summary' => null,
            ]
        );
        $this->contribRepo->add($contrib5);


        /**
         * Categories
         */


        $cat0 = new AppObject(
            [
            'id' => 'cat',
            'name' => 'Une catégorie',
            'parent_id' => null,
            ]
        );
        $this->catRepo->add($cat0);

        $cat1 = new AppObject(
            [
            'id' => 'another-cat',
            'name' => 'Une autre catégorie',
            'parent_id' => null,
            ]
        );
        $this->catRepo->add($cat1);


        /**
         * Books
         */


        $book1 = new AppObject(
            [
            'id' => 'a-book',
            'title' => 'Mon premier livre !',
            ]
        );
        $this->bookRepo->add($book1);

        $book2 = new AppObject(
            [
            'id' => 'another-book',
            'title' => 'Mon deuxième livre…',
            ]
        );
        $this->bookRepo->add($book2);


        /**
         * Chapters
         */


        $chapter1 = new AppObject(
            [
            'id' => 'chapter-1',
            'book_id' => 'a-book',
            'title' => 'Au commencement…',
            'order' => 1,
            ]
        );
        $this->chapterRepo->add($chapter1);

        $chapter2 = new AppObject(
            [
            'id' => 'chapter-2',
            'book_id' => 'a-book',
            'title' => 'Puis ensuite',
            'order' => 2,
            ]
        );
        $this->chapterRepo->add($chapter2);

        $chapter3 = new AppObject(
            [
            'id' => 'chapter-3',
            'book_id' => 'a-book',
            'title' => 'Conclusion…',
            'order' => 3,
            ]
        );
        $this->chapterRepo->add($chapter3);


        /**
         * Articles
         */


        $article1 = new AppObject(
            [
            'id' => 'nouvel-article',
            'author_id' => $loulimi['id'],
            'category_id' => $cat0['id'],
            'body' => file_get_contents(dirname(__FILE__) . '/../../fixtures/article.md'),
            'is_featured' => true,
            'is_published' => true,
            'title' => 'Crossed Paths v3.8.8',
            'sub_title' => null,
            'cover_filename' => 'crossed-paths.webp',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article1);
        
        $article2 = new AppObject(
            [
            'id' => 'nouvel-version-tcm',
            'author_id' => $loulimi['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'TCM',
            'sub_title' => '4:9.0',
            'cover_filename' => 'tcm.webp',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article2);
        
        $article3 = new AppObject(
            [
            'id' => 'prout-lol-xptdr',
            'author_id' => $neophus['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'Encore un autre article',
            'sub_title' => null,
            'cover_filename' => 'encore-un-autre-article.webp',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article3);
        
        $article4 = new AppObject(
            [
            'id' => 'bonjour-a-tous',
            'author_id' => $valve['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'L’inspiration c’est pas mon truc',
            'sub_title' => 'mais genre pas du tout',
            'cover_filename' => 'tcm.webp',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article4);
        
        $article5 = new AppObject(
            [
            'id' => 'commencement',
            'author_id' => $scTeam['id'],
            'category_id' => $cat1['id'],
            'body' => 'Ceci est le tout début… Bienvenue à tout le monde ! Commencons sans plus attendre. Blah blah blah…',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Se préparer…',
            'sub_title' => 'mais genre pas du tout',
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article5);
        
        $article6 = new AppObject(
            [
            'id' => 'la-compet',
            'author_id' => $loulimi['id'],
            'category_id' => $cat1['id'],
            'body' => 'Rien de plus important que de réussir sa compétition. Une fois le jour J arrivé, tout l’entraînement n’aura servi à rien si l’on ne donne pas son maximum.',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Au moment de la compétition',
            'sub_title' => null,
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article6);

        $article7 = new AppObject(
            [
            'id' => 'article-with-thumbnail',
            'author_id' => $neophus['id'],
            'category_id' => $cat1['id'],
            'body' => '',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Just another title',
            'sub_title' => null,
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => '2021112713481.jpg',
            ]
        );
        $this->articleRepo->add($article7);

        $article8 = new AppObject(
            [
            'id' => 'goldsource-review',
            'author_id' => $neophus['id'],
            'category_id' => $cat1['id'],
            'body' => 'Un moteur tout à fait génial !!!!',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'GoldSource : pour ou contre ?',
            'sub_title' => 'Grande question',
            'cover_filename' => 'goldsource-pour-ou-contre.webp',
            'thumbnail_filename' => null,
            ]
        );
        $this->articleRepo->add($article8);

        $article9 = new AppObject(
            [
            'id'=> 'crossed-paths',
            'author_id'=> $valve['id'],
            'category_id'=> $cat1['id'],
            'body' => file_get_contents($this->config->getPathOfAppDirectory() .'/fixtures/crossed_paths.md'),
            'is_featured' => true,
            'is_published' => true,
            'title'=> 'Crossed Paths',
            'sub_title' => 'Une campagne pleine de surprises !',
            'cover_filename' => 'crossed-paths.webp',
            'thumbnail_filename'=> null,
            ]
        );
        $this->articleRepo->add($article9);

        /**
         * Reviews
         */

        $review1 = new AppObject(
            [
            'id' => null,
            'article_id' => $article1['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.md'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.md'),
            ]
        );
        $this->reviewRepo->add($review1);

        $review2 = new AppObject(
            [
            'id' => null,
            'article_id' => $article2['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.md'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.md'),
            ]
        );
        $this->reviewRepo->add($review2);

        $review3 = new AppObject(
            [
            'id' => null,
            'article_id' => $article3['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.md'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.md'),
            ]
        );
        $this->reviewRepo->add($review3);

        $review4 = new AppObject(
            [
            'id' => null,
            'article_id' => $article4['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.md'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.md'),
            ]
        );
        $this->reviewRepo->add($review4);

        $review5 = new AppObject(
            [
            'id' => null,
            'article_id' => 'goldsource-review',
            'playable_id' => 'goldsource',
            'rating' => 5,
            'body' =>  'Un moteur parfait',
            'cons' => '- Aucun',
            'pros' => '- Tout',
            ]
        );
        $this->reviewRepo->add($review5);

        $review6 = new AppObject(
            [
            'id'=> null,
            'article_id' => 'crossed-paths',
            'playable_id' => 'crossed-paths',
            'rating' => 5,
            'body' => 'Une campagne riche et maîtrisée incluant de nombreux rebondissements et pleins de détails à découvrir offrant une belle re-jouabilité .',
            'cons' => " - Peu de variété d'ennemis.\n - Les puzzles et les situations qui parfois peuvent être difficiles à comprendre.",
            'pros' => " - Qualité du mapping.\n - Puzzle et détails très intéressants et bien mis en scène.\n - Une bonne re-jouabilité.",
            ]
        );
        $this->reviewRepo->add($review6);

        
        $this->dbManager->getPdo()->commit();
    }
}