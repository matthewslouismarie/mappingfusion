<?php

namespace MF\Database;

use DateTimeImmutable;
use LM\WebFramework\Configuration;
use LM\WebFramework\DataStructures\AppObject;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\BookRepository;
use MF\Repository\CategoryRepository;
use MF\Repository\ChapterRepository;
use MF\Repository\ContributionRepository;
use MF\Repository\MemberRepository;
use MF\Repository\PlayableLinkRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;

class Fixture
{
    public function __construct(
        private ArticleRepository $repoArticle,
        private AuthorRepository $repoAuthor,
        private BookRepository $repoBook,
        private CategoryRepository $repoCat,
        private ChapterRepository $repoChapter,
        private Configuration $config,
        private ContributionRepository $repoContrib,
        private DatabaseManager $conn,
        private MemberRepository $repoMember,
        private PlayableLinkRepository $linkRepo,
        private PlayableRepository $repoPlayable,
        private ReviewRepository $repoReview,
    ) {
    }

    public function load(): void {
        $this->conn->getPdo()->beginTransaction();

        /**
         * Authors
         */


        $loulimi = new AppObject([
            'id' => 'root',
            'name' => 'Root',
            'avatar_filename' => null,
        ]);
        $this->repoAuthor->add($loulimi);

        $valve = new AppObject([
            'id' => 'valve',
            'name' => 'Valve',
            'avatar_filename' => null,
        ]);
        $this->repoAuthor->add($valve);

        $scTeam = new AppObject([
            'id' => 'sven-co-op-team',
            'name' => 'The Sven Co-op Team',
            'avatar_filename' => null,
        ]);
        $this->repoAuthor->add($scTeam);

        $neophus = new AppObject([
            'id' => 'neophus',
            'name' => 'Neophus',
            'avatar_filename' => null,
        ]);
        $this->repoAuthor->add($neophus);


        /**
         * Accounts
         */


        $root = new AppObject([
            'id' => 'root',
            'password' => password_hash($this->config->getSetting('rootMemberPwd'), PASSWORD_DEFAULT),
            'author_id' => 'root',
        ]);
        $this->repoMember->add($root);


        /**
         * Playables
         */


        $gs = new AppObject([
            'id' => 'goldsource',
			'name' => 'GoldSource',
			'release_date_time' => new DateTimeImmutable(),
			'game_id' => null,
            'type' => PlayableType::Standalone->value,
        ]);
        $this->repoPlayable->add($gs);

        $hl = new AppObject([
            'id' => 'half-life',
			'name' => 'Half-Life',
			'release_date_time' => new DateTimeImmutable(),
			'game_id' => 'goldsource',
            'type' => PlayableType::Standalone->value,
        ]);
        $this->repoPlayable->add($hl);

        $hl2 = new AppObject([
            'id' => 'half-life-2',
			'name' => 'Half-Life 2',
			'release_date_time' => new DateTimeImmutable(),
			'game_id' => null,
            'type' => PlayableType::Standalone->value,
        ]);
        $this->repoPlayable->add($hl2);

        $sc = new AppObject([
            'id' => 'sven-co-op',
			'name' => 'Sven Co-op',
			'release_date_time' => new DateTimeImmutable(),
			'game_id' => 'goldsource',
            'type' => PlayableType::Standalone->value,
        ]);
        $this->repoPlayable->add($sc);

        $cp = new AppObject([
            'id' => 'crossed-paths',
			'name' => 'Crossed Paths',
			'release_date_time' => new DateTimeImmutable(),
			'game_id' => 'sven-co-op',
            'type' => PlayableType::Map->value,
        ]);
        $this->repoPlayable->add($cp);


        /**
         * Playable links
         */


        $link1 = new AppObject([
            'id' => null,
            'playable_id' => $sc['id'],
            'name' => 'Homepage',
            'type' => LinkType::HomePage->name,
            'url' => 'https://svencoop.com',
        ]);
        $this->linkRepo->add($link1);

        $link2 = new AppObject([
            'id' => null,
            'playable_id' => $sc['id'],
            'name' => 'Download',
            'type' => LinkType::Download->name,
            'url' => 'https://store.steampowered.com/agecheck/app/225840/',
        ]);
        $this->linkRepo->add($link2);


        /**
         * Contributions
         */


        $contrib1 = new AppObject([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add($contrib1);

        $contrib2 = new AppObject([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add($contrib2);

        $contrib3 = new AppObject([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add($contrib3);

        $contrib4 = new AppObject([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl2['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add($contrib4);

        $contrib5 = new AppObject([
            'id' => null,
            'author_id' => $scTeam['id'],
            'playable_id' => $sc['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add($contrib5);


        /**
         * Categories
         */


        $cat0 = new AppObject([
            'id' => 'cat',
            'name' => 'Une catégorie',
            'parent_id' => null,
        ]);
        $this->repoCat->add($cat0);

        $cat1 = new AppObject([
            'id' => 'another-cat',
            'name' => 'Une autre catégorie',
            'parent_id' => null,
        ]);
        $this->repoCat->add($cat1);


        /**
         * Books
         */


        $book1 = new AppObject([
            'id' => 'a-book',
            'title' => 'Mon premier livre !',
        ]);
        $this->repoBook->add($book1);

        $book2 = new AppObject([
            'id' => 'another-book',
            'title' => 'Mon deuxième livre…',
        ]);
        $this->repoBook->add($book2);


        /**
         * Chapters
         */


        $chapter1 = new AppObject([
            'id' => 'chapter-1',
            'book_id' => 'a-book',
            'title' => 'Au commencement…',
            'order' => 1,
        ]);
        $this->repoChapter->add($chapter1);

        $chapter2 = new AppObject([
            'id' => 'chapter-2',
            'book_id' => 'a-book',
            'title' => 'Puis ensuite',
            'order' => 2,
        ]);
        $this->repoChapter->add($chapter2);

        $chapter3 = new AppObject([
            'id' => 'chapter-3',
            'book_id' => 'a-book',
            'title' => 'Conclusion…',
            'order' => 3,
        ]);
        $this->repoChapter->add($chapter3);


        /**
         * Articles
         */


        $article1 = new AppObject([
            'id' => 'nouvel-article',
            'author_id' => $root['id'],
            'category_id' => $cat0['id'],
            'body' => file_get_contents(dirname(__FILE__) . '/../../fixtures/article.mk'),
            'is_featured' => true,
            'is_published' => true,
            'title' => 'Crossed Paths v3.8.8',
            'sub_title' => null,
            'cover_filename' => '202111271344571.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article1);
        
        $article2 = new AppObject([
            'id' => 'nouvel-version-tcm',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'TCM',
            'sub_title' => '4:9.0',
            'cover_filename' => '202111271344571.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article2);
        
        $article3 = new AppObject([
            'id' => 'prout-lol-xptdr',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'Encore un autre article',
            'sub_title' => null,
            'cover_filename' => '202201051906201.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article3);
        
        $article4 = new AppObject([
            'id' => 'bonjour-a-tous',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'L’inspiration c’est pas mon truc',
            'sub_title' => 'mais genre pas du tout',
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article4);
        
        $article5 = new AppObject([
            'id' => 'commencement',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'Ceci est le tout début… Bienvenue à tout le monde ! Commencons sans plus attendre. Blah blah blah…',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Se préparer…',
            'sub_title' => 'mais genre pas du tout',
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article5);
        
        $article6 = new AppObject([
            'id' => 'la-compet',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'Rien de plus important que de réussir sa compétition. Une fois le jour J arrivé, tout l’entraînement n’aura servi à rien si l’on ne donne pas son maximum.',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Au moment de la compétition',
            'sub_title' => null,
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => null,
        ]);
        $this->repoArticle->add($article6);

        $article7 = new AppObject([
            'id' => 'article-with-thumbnail',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => '',
            'is_featured' => false,
            'is_published' => true,
            'title' => 'Just another title',
            'sub_title' => null,
            'cover_filename' => '202111271348081.jpg',
            'thumbnail_filename' => '2021112713481.jpg',
        ]);
        $this->repoArticle->add($article7);

        $article8 = new AppObject([
            'id' => 'goldsource-review',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'Un moteur tout à fait génial !!!!',
            'is_featured' => true,
            'is_published' => true,
            'title' => 'GoldSource : pour ou contre ?',
            'sub_title' => 'Grande question',
            'cover_filename' => '202111271344571.jpg',
            'thumbnail_filename' => '202111271348081.jpg',
        ]);
        $this->repoArticle->add($article8);


        /**
         * Reviews
         */

        $review1 = new AppObject([
            'id' => null,
            'article_id' => $article1['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]);
        $this->repoReview->add($review1);

        $review2 = new AppObject([
            'id' => null,
            'article_id' => $article2['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]);
        $this->repoReview->add($review2);

        $review3 = new AppObject([
            'id' => null,
            'article_id' => $article3['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]);
        $this->repoReview->add($review3);

        $review4 = new AppObject([
            'id' => null,
            'article_id' => $article4['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]);
        $this->repoReview->add($review4);

        $review5 = new AppObject([
            'id' => null,
            'article_id' => 'goldsource-review',
            'playable_id' => 'goldsource',
            'rating' => 5,
            'body' =>  'Un moteur parfait',
            'cons' => '- Aucun',
            'pros' => '- Tout',
        ]);
        $this->repoReview->add($review5);
        
        $this->conn->getPdo()->commit();
    }
}