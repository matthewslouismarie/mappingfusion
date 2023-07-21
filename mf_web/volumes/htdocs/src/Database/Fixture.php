<?php

namespace MF\Database;

use DateTimeImmutable;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Model\Article;
use MF\Model\Author;
use MF\Model\Category;
use MF\Model\Contribution;
use MF\Model\Member;
use MF\Model\PasswordHash;
use MF\Model\Playable;
use MF\Model\PlayableLink;
use MF\Model\Review;
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
        $root = new Member('root', new PasswordHash(clear: $this->config->getSetting('rootMemberPwd')));
        $this->repoMember->add($root);
        $loulimi = new Author(null, 'Loulimi');
        $neophus = new Author(null, 'Neophus');
        $scteam = new Author(null, 'The Sven Co-op Team');
        $valve = new Author(null, 'Valve');
        $this->repoAuthor->add($scteam);
        $this->repoAuthor->add($valve);
        $this->repoAuthor->add($loulimi);
        $this->repoAuthor->add($neophus);
        $gs = new Playable(null, 'GoldSource', new DateTimeImmutable(), null);
        $hl = new Playable(null, 'Half-Life', new DateTimeImmutable(), $gs->getId());
        $hl2 = new Playable(null, 'Half-Life 2', new DateTimeImmutable(), $gs->getId());
        $sc = new Playable(null, 'Sven Co-op', new DateTimeImmutable(), $gs->getId());
        $crossedPaths = new Playable(null, 'Crossed Paths', new DateTimeImmutable(), $gs->getId());
        $this->repoPlayable->add($gs);
        $this->repoPlayable->add($hl);
        $this->repoPlayable->add($hl2);
        $this->repoPlayable->add($sc);
        $this->repoPlayable->add($crossedPaths);
        $this->linkRepo->add(new PlayableLink(null, $sc->getId(), 'Homepage', LinkType::HomePage, 'https://svencoop.com'));
        $this->linkRepo->add(new PlayableLink(null, $sc->getId(), 'Download', LinkType::Download, 'https://store.steampowered.com/agecheck/app/225840/'));
        $this->repoContrib->add(new Contribution(null, $loulimi->getId(), $crossedPaths->getId(), true));
        $this->repoContrib->add(new Contribution(null, $neophus->getId(), $crossedPaths->getId(), true));
        $this->repoContrib->add(new Contribution(null, $valve->getId(), $hl->getId(), true));
        $this->repoContrib->add(new Contribution(null, $valve->getId(), $hl2->getId(), true));
        $this->repoContrib->add(new Contribution(null, $scteam->getId(), $sc->getId(), true));
        $cat = new Category(null, 'Tests');
        $this->repoCat->add($cat);

        $article0 = new Article(
            null,
            $root->getId(),
            $cat->getId(),
            file_get_contents(dirname(__FILE__) . '/../../fixtures/article.mk'),
            true,
            'Crossed Paths v3.8.8',
            '202111271344571.jpg',
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
        $this->repoArticle->addNewArticle($article0);

        $article1 = new Article(
            null,
            $root->getId(),
            $cat->getId(),
            'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            true,
            'Nouvelle version  \nTCM v387823.3223.1',
            '202111271344571.jpg',
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
        $this->repoArticle->addNewArticle($article1);

        $article2 = new Article(
            null,
            $root->getId(),
            $cat->getId(),
            'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            true,
            'Un autre test',
            '202201051906201.jpg',
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
        $this->repoArticle->addNewArticle($article2);

        $article3 = new Article(
            null,
            $root->getId(),
            $cat->getId(),
            'The Crystal Mission a reçu une nouvelle mise à jour, et franchement elle vaut le coup de rejouer à la map.',
            true,
            'En avant !',
            '202111271348081.jpg',
            new DateTimeImmutable(),
            new DateTimeImmutable(),
        );
        $this->repoArticle->addNewArticle($article3);

        $this->repoReview->add(new Review(
            null,
            $article0->getId(),
            $sc->getId(),
            4,
            'En somme, un jeu vraiment pas mal. Je recommande.',
            file_get_contents(dirname(__FILE__) . '/../../fixtures/cons.mk'),
            file_get_contents(dirname(__FILE__) . '/../../fixtures/pros.mk'),
        ));
        $this->repoReview->add(new Review(null, $article1->getId(), $sc->getId(), 5, '', '', ''));
        $this->repoReview->add(new Review(null, $article2->getId(), $hl->getId(), 3.1, '', '', ''));
        $this->repoReview->add(new Review(null, $article3->getId(), $hl2->getId(), 2.1, '', '', ''));
        $this->conn->getPdo()->commit();
    }
}