<?php

namespace MF\Database;

use DateTimeImmutable;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Model\ArticleModel;
use MF\Model\CategoryModel;
use MF\Model\PasswordHash;
use MF\Model\PlayableLinkModel;
use MF\Model\PlayableModel;
use MF\Model\ReviewModel;
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
    
        $root = [
            'id' => 'root',
            'password_hash' => (new PasswordHash(clear: $this->config->getSetting('rootMemberPwd')))->__toString(),
        ];
        $this->repoMember->add($root);

        $loulimi = ['id' => 'loulimi', 'name' => 'Loulimi'];
        $valve = ['id' => 'valve', 'name' => 'Valve'];
        $scTeam = ['id' => 'sven-co-op-team', 'name' => 'The Sven Co-op Team'];
        $neophus = ['id' => 'neophus', 'name' => 'Neophus'];
        $this->repoAuthor->add($loulimi);
        $this->repoAuthor->add($valve);
        $this->repoAuthor->add($scTeam);
        $this->repoAuthor->add($neophus);

        $gs = ['id' => 'goldsource', 'name' => 'GoldSource', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null];
        $hl = ['id' => 'half-life', 'name' => 'Half-Life', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource'];
        $hl2 = ['id' => 'half-life-2', 'name' => 'Half-Life 2', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null];
        $sc = ['id' => 'sven-co-op', 'name' => 'Sven Co-op', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource'];
        $cp = ['id' => 'crossed-paths', 'name' => 'Crossed Paths', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'sven-co-op'];
        $model = new PlayableModel();
        $this->repoPlayable->add($this->factory->create($gs, $model));
        $this->repoPlayable->add($this->factory->create($hl, $model));
        $this->repoPlayable->add($this->factory->create($hl2, $model));
        $this->repoPlayable->add($this->factory->create($sc, $model));
        $this->repoPlayable->add($this->factory->create($cp, $model));

        $linkModel = new PlayableLinkModel();
        $this->linkRepo->add($this->factory->create([
           'id' => null,
           'playable_id' => $sc['id'],
           'name' => 'Homepage',
           'type' => LinkType::HomePage->name,
           'url' => 'https://svencoop.com',
        ], $linkModel));
        $this->linkRepo->add($this->factory->create([
           'id' => null,
           'playable_id' => $sc['id'],
           'name' => 'Download',
           'type' => LinkType::Download->name,
           'url' => 'https://store.steampowered.com/agecheck/app/225840/',
        ], $linkModel));

        $this->repoContrib->add([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add([
            'id' => null,
            'author_id' => $loulimi['id'],
            'playable_id' => $cp['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add([
            'id' => null,
            'author_id' => $valve['id'],
            'playable_id' => $hl2['id'],
            'is_author' => true,
            'summary' => null,
        ]);
        $this->repoContrib->add([
            'id' => null,
            'author_id' => $scTeam['id'],
            'playable_id' => $sc['id'],
            'is_author' => true,
            'summary' => null,
        ]);

        $catModel = new CategoryModel();
        $cat0 = [
            'id' => 'cat',
            'name' => 'Une catégorie',
        ];
        $cat1 = [
            'id' => 'another-cat',
            'name' => 'Une autre catégorie',
        ];
        $this->repoCat->add($this->factory->create($cat0, $catModel));
        $this->repoCat->add($this->factory->create($cat1, $catModel));

        $articleModel = new ArticleModel();
        $article0 = [
            'id' => 'nouvel-article',
            'author_id' => $root['id'],
            'category_id' => $cat0['id'],
            'body' => file_get_contents(dirname(__FILE__) . '/../../fixtures/article.mk'),
            'is_featured' => true,
            'title' => 'Crossed Paths v3.8.8',
            'sub_title' => null,
            'cover_filename' => '202111271344571.jpg',
            'creation_date_time' => new DateTimeImmutable(),
            'last_update_date_time' => new DateTimeImmutable(),
        ];
        $this->repoArticle->add($this->factory->create($article0, $articleModel));
        
        $article1 = [
            'id' => 'nouvel-version-tcm',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'title' => 'TCM',
            'sub_title' => '4:9.0',
            'cover_filename' => '202111271344571.jpg',
            'creation_date_time' => new DateTimeImmutable(),
            'last_update_date_time' => new DateTimeImmutable(),
        ];
        $this->repoArticle->add($this->factory->create($article1, $articleModel));
        
        $article2 = [
            'id' => 'prout-lol-xptdr',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => true,
            'title' => 'Encore un autre article',
            'sub_title' => null,
            'cover_filename' => '202201051906201.jpg',
            'creation_date_time' => new DateTimeImmutable(),
            'last_update_date_time' => new DateTimeImmutable(),
        ];
        $this->repoArticle->add($this->factory->create($article2, $articleModel));
        
        $article3 = [
            'id' => 'bonjour-a-tous',
            'author_id' => $root['id'],
            'category_id' => $cat1['id'],
            'body' => 'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            'is_featured' => false,
            'title' => 'L’inspiration c’est pas mon truc',
            'sub_title' => 'mais genre pas du tout',
            'cover_filename' => '202111271348081.jpg',
            'creation_date_time' => new DateTimeImmutable(),
            'last_update_date_time' => new DateTimeImmutable(),
        ];
        $this->repoArticle->add($this->factory->create($article3, $articleModel));

        $reviewModel = new ReviewModel();
        $this->repoReview->add($this->factory->create([
            'id' => null,
            'article_id' => $article0['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ], $reviewModel));
        $this->repoReview->add($this->factory->create([
            'id' => null,
            'article_id' => $article1['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ], $reviewModel));
        $this->repoReview->add($this->factory->create([
            'id' => null,
            'article_id' => $article2['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ], $reviewModel));
        $this->repoReview->add($this->factory->create([
            'id' => null,
            'article_id' => $article3['id'],
            'playable_id' => $sc['id'],
            'rating' => 4,
            'body' =>  'En somme, un jeu vraiment pas mal. Je recommande.',
            'cons' => file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            'pros' => file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ], $reviewModel));
        $this->conn->getPdo()->commit();
    }
}