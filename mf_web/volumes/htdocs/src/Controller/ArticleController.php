<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements ControllerInterface
{
    const ROUTE_ID = 'article';

    public function __construct(
        private AuthorRepository $authorRepo,
        private ArticleRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $article = $this->repo->find($routeParams[1]);

        if (null === $article) {
            throw new NotFoundException();
        }

        return new Response(
            body: $this->twig->render('article.html.twig', [
                'article' => $article,
                'authors' => isset($article->review) ? $this->authorRepo->findAuthorsOf($article->review['playable_id']) : null,
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}