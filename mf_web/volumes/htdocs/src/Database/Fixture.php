<?php

namespace MF\Database;

use MF\Configuration;
use MF\Model\Author;
use MF\Model\Contribution;
use MF\Model\Member;
use MF\Model\PasswordHash;
use MF\Model\Playable;
use MF\Repository\AuthorRepository;
use MF\Repository\CategoryRepository;
use MF\Repository\ContributionRepository;
use MF\Repository\MemberRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;

class Fixture
{
    public function __construct(
        private Configuration $config,
        private AuthorRepository $repoAuthor,
        private CategoryRepository $repoCat,
        private ContributionRepository $repoContrib,
        private MemberRepository $repoMember,
        private PlayableRepository $repoPlayable,
        private ReviewRepository $repoReview,
    ) {
    }

    public function load(): void {
        $this->repoMember->add(new Member('root', new PasswordHash(clear: $this->config->getSetting('rootMemberPwd'))));
        $loulimi = new Author(null, 'Loulimi');
        $neophus = new Author(null, 'Neophus');
        $scteam = new Author(null, 'The Sven Co-op Team');
        $valve = new Author(null, 'Valve');
        $this->repoAuthor->add($scteam);
        $this->repoAuthor->add($valve);
        $this->repoAuthor->add($loulimi);
        $this->repoAuthor->add($neophus);
        $this->repoPlayable->add(new Playable(null, 'Sven Co-op', null));
        $this->repoPlayable->add(new Playable(null, 'Half-Life', null));
        $this->repoPlayable->add(new Playable(null, 'Half-Life 2', null));
        $crossedPaths = new Playable(null, 'Crossed Paths', null);
        $this->repoPlayable->add($crossedPaths);
        $this->repoContrib->add(new Contribution(null, $loulimi->getId(), $crossedPaths->getId(), true));
        $this->repoContrib->add(new Contribution(null, $neophus->getId(), $crossedPaths->getId(), true));
    }
}