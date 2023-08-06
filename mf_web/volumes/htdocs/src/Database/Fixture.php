<?php

namespace MF\Database;

use DateTimeImmutable;
use MF\Configuration;
use MF\DataStructure\AppObject;
use MF\Enum\LinkType;
use MF\Model\AuthorModel;
use MF\Model\CategoryModel;
use MF\Model\ContributionModel;
use MF\Model\MemberModel;
use MF\Model\PasswordHash;
use MF\Model\PlayableLinkModel;
use MF\Model\PlayableModel;
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
    
        $memberModel = new MemberModel();
        $root = new AppObject(
            [
                'id' => 'root',
                'password_hash' => (new PasswordHash(clear: $this->config->getSetting('rootMemberPwd')))->__toString(),
            ],
            $memberModel,
        );
        $this->repoMember->add($root);

        $authorModel = new AuthorModel();
        $loulimi = new AppObject(['id' => 'loulimi', 'name' => 'Loulimi'], $authorModel);
        $valve = new AppObject(['id' => 'valve', 'name' => 'Valve'], $authorModel);
        $scTeam = new AppObject(['id' => 'sven-co-op-team', 'name' => 'The Sven Co-op Team'], $authorModel);
        $neophus = new AppObject(['id' => 'neophus', 'name' => 'Neophus'], $authorModel);
        $this->repoAuthor->add($loulimi);
        $this->repoAuthor->add($valve);
        $this->repoAuthor->add($scTeam);
        $this->repoAuthor->add($neophus);

        $playableModel = new PlayableModel();
        $gs = new AppObject(['id' => 'goldsource', 'name' => 'GoldSource', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null], $playableModel);
        $hl = new AppObject(['id' => 'half-life', 'name' => 'Half-Life', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource'], $playableModel);
        $hl2 = new AppObject(['id' => 'half-life-2', 'name' => 'Half-Life 2', 'release_date_time' => new DateTimeImmutable(), 'game_id' => null], $playableModel);
        $sc = new AppObject(['id' => 'sven-co-op', 'name' => 'Sven Co-op', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'goldsource'], $playableModel);
        $cp = new AppObject(['id' => 'crossed-paths', 'name' => 'Crossed Paths', 'release_date_time' => new DateTimeImmutable(), 'game_id' => 'sven-co-op'], $playableModel);
        $this->repoPlayable->add($gs);
        $this->repoPlayable->add($hl);
        $this->repoPlayable->add($hl2);
        $this->repoPlayable->add($sc);
        $this->repoPlayable->add($cp);

        $linkModel = new PlayableLinkModel();
        $this->linkRepo->add(new AppObject([
           'id' => null,
           'playable_id' => $sc->id,
           'name' => 'Homepage',
           'type' => LinkType::HomePage->name,
           'url' => 'https://svencoop.com',
        ], $linkModel));
        $this->linkRepo->add(new AppObject([
           'id' => null,
           'playable_id' => $sc->id,
           'name' => 'Download',
           'type' => LinkType::Download->name,
           'url' => 'https://store.steampowered.com/agecheck/app/225840/',
        ], $linkModel));

        $contribModel = new ContributionModel();
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $loulimi->id,
            'playable_id' => $cp->id,
            'is_author' => true,
            'summary' => null,
        ], $contribModel));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $loulimi->id,
            'playable_id' => $cp->id,
            'is_author' => true,
            'summary' => null,
        ], $contribModel));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $valve->id,
            'playable_id' => $hl->id,
            'is_author' => true,
            'summary' => null,
        ], $contribModel));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $valve->id,
            'playable_id' => $hl2->id,
            'is_author' => true,
            'summary' => null,
        ], $contribModel));
        $this->repoContrib->add(new AppObject([
            'id' => null,
            'author_id' => $scTeam->id,
            'playable_id' => $sc->id,
            'is_author' => true,
            'summary' => null,
        ], $contribModel));

        $catModel = new CategoryModel();
        $this->repoCat->add(new AppObject([
            'id' => 'cat',
            'name' => 'Une catégorie',
        ], $catModel));

        // $article0 = new Article(
        //     null,
        //     $root->getId(),
        //     $cat->getId(),
        //     file_get_contents(dirname(__FILE__) . '/../../fixtures/article.mk'),
        //     true,
        //     'Crossed Paths v3.8.8',
        //     '202111271344571.jpg',
        //     new DateTimeImmutable(),
        //     new DateTimeImmutable(),
        // );
        // $this->repoArticle->addNewArticle($article0);

        // $article1 = new Article(
        //     null,
        //     $root->getId(),
        //     $cat->getId(),
        //     'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
        //     true,
        //     'Nouvelle version  \nTCM v387823.3223.1',
        //     '202111271344571.jpg',
        //     new DateTimeImmutable(),
        //     new DateTimeImmutable(),
        // );
        // $this->repoArticle->addNewArticle($article1);

        // $article2 = new Article(
        //     null,
        //     $root->getId(),
        //     $cat->getId(),
        //     'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
        //     true,
        //     'Un autre test',
        //     '202201051906201.jpg',
        //     new DateTimeImmutable(),
        //     new DateTimeImmutable(),
        // );
        // $this->repoArticle->addNewArticle($article2);

        // $article3 = new Article(
        //     null,
        //     $root->getId(),
        //     $cat->getId(),
        //     'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
        //     true,
        //     'En avant !',
        //     '202111271348081.jpg',
        //     new DateTimeImmutable(),
        //     new DateTimeImmutable(),
        // );
        // $this->repoArticle->addNewArticle($article3);

        // $this->repoReview->add(new Review(
        //     null,
        //     $article0->getId(),
        //     $sc->getId(),
        //     4,
        //     'En somme, un jeu vraiment pas mal. Je recommande.',
        //     file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
        //     file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        // ));
        // $this->repoReview->add(new Review(null, $article1->getId(), $sc->getId(), 5, '', '', ''));
        // $this->repoReview->add(new Review(null, $article2->getId(), $hl->getId(), 3.1, '', '', ''));
        // $this->repoReview->add(new Review(null, $article3->getId(), $hl2->getId(), 2.1, '', '', ''));
        $this->conn->getPdo()->commit();
    }
}