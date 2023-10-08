<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Exception\Http\BadRequestException;
use MF\Exception\Http\NotFoundException;
use MF\Repository\ArticleRepository;
use MF\Repository\MemberRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController implements ControllerInterface
{
    const ROUTE_ID = 'membre';

    public function __construct(
        private ArticleRepository $articleRepository,
        private MemberRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        if (!key_exists(1, $routeParams)) {
            throw new BadRequestException();
        }
        $member = $this->repo->find($routeParams[1]);

        if (null === $member) {
            throw new NotFoundException();
        }

        $articles = $this->articleRepository->findArticlesFrom($member->id);

        return new Response(
            body: $this->twig->render('member.html.twig', [
                'articles' => $articles,
                'member' => $member,
            ])
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}