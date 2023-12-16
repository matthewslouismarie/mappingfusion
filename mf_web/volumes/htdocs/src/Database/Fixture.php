<?php

namespace MF\Database;

use DateTimeImmutable;
use MF\Configuration;
use MF\Framework\DataStructures\AppObject;
use MF\Enum\LinkType;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\CategoryRepository;
use MF\Repository\ContributionRepository;
use MF\Repository\MemberRepository;
use MF\Repository\PlayableLinkRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;

class Fixture
{
    public function __construct(
        private Configuration $config,
        private DatabaseManager $conn,
        private ArticleRepository $repoArticle,
        private AuthorRepository $repoAuthor,
        private CategoryRepository $repoCat,
        private ContributionRepository $repoContrib,
        private MemberRepository $repoMember,
        private PlayableLinkRepository $linkRepo,
        private PlayableRepository $repoPlayable,
        private ReviewRepository $repoReview,
    ) {
    }

    public function load(): void {
        $this->conn->getPdo()->beginTransaction();

        $loulimi = new AppObject(['id' => 'root', 'name' => 'Root']);
        $valve = new AppObject(['id' => 'valve', 'name' => 'Valve']);
        $scTeam = new AppObject(['id' => 'sven-co-op-team', 'name' => 'The Sven Co-op Team']);
        $neophus = new AppObject(['id' => 'neophus', 'name' => 'Neophus']);
        $this->repoAuthor->add($loulimi);
        $this->repoAuthor->add($valve);
        $this->repoAuthor->add($scTeam);
        $this->repoAuthor->add($neophus);

        $root = new AppObject([
            'id' => 'root',
            'password' => password_hash($this->config->getSetting('rootMemberPwd'), PASSWORD_DEFAULT),
            'author_id' => 'root',
        ]);
        $this->repoMember->add($root);

        $gs = new AppObject(['id' => 'goldsource', 'name' => 'GoldSource', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null]);
        $hl = new AppObject(['id' => 'half-life', 'name' => 'Half-Life', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource']);
        $hl2 = new AppObject(['id' => 'half-life-2', 'name' => 'Half-Life 2', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null]);
        $sc = new AppObject(['id' => 'sven-co-op', 'name' => 'Sven Co-op', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource']);
        $cp = new AppObject(['id' => 'crossed-paths', 'name' => 'Crossed Paths', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'sven-co-op']);

        $this->repoPlayable->add($gs);
        $this->repoPlayable->add($hl);
        $this->repoPlayable->add($hl2);
        $this->repoPlayable->add($sc);
        $this->repoPlayable->add($cp);

        $this->linkRepo->add(new AppObject([
           'id' => null,
           'playable_id' => $sc['id'],
           'name' => 'Homepage',
           'type' => LinkType::HomePage->name,
           'url' => 'https://svencoop.com',
        ]));
        $this->linkRepo->add(new AppObject([
           'id' => null,
           'playable_id' => $sc['id'],
           'name' => 'Download',
           'type' => LinkType::Download->name,
           'url' => 'https://store.steampowered.com/agecheck/app/225840/',
        ]));

        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl['id'],
            'is_author' => true,
            'summary' => null,
        ]));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl2['id'],
            'is_author' => true,
            'summary' => null,
        ]));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $scTeam['id'],
            'playable_id' => $sc['id'],
            'is_author' => true,
            'summary' => null,
        ]));

        $cat0 = new AppObject([
            'id' => 'cat',
            'name' => 'Une catégorie',
        ]);
        $cat1 = new AppObject([
            'id' => 'another-cat',
            'name' => 'Une autre catégorie',
        ]);
        $this->repoCat->add($cat0);
        $this->repoCat->add($cat1);

        $article0 = new AppObject([
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
        $this->repoArticle->add($article0);
        
        $article1 = new AppObject([
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
        $this->repoArticle->add($article1);
        
        $article2 = new AppObject([
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
        $this->repoArticle->add($article2);
        
        $article3 = new AppObject([
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
        $this->repoArticle->add($article3);

        $this->repoArticle->add(new AppObject([
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
        ]));

        $this->repoReview->add(new AppObject([
            'id' => null,
            'article_id' => $article0['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]));
        $this->repoReview->add(new AppObject([
            'id' => null,
            'article_id' => $article1['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]));
        $this->repoReview->add(new AppObject([
            'id' => null,
            'article_id' => $article2['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]));
        $this->repoReview->add(new AppObject([
            'id' => null,
            'article_id' => $article3['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ]));
        $this->conn->getPdo()->commit();
    }
}