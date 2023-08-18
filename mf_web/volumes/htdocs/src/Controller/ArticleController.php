<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements ControllerInterface
{
    const ROUTE_ID = 'view-article';

    public function __construct(
        private AuthorRepository $authorRepo,
        private ArticleRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $article = $this->repo->findOne($routeParams[1]);

        var_dump($article->review);
        var_dump(isset($article->review));

        return new Response(
            body: $this->twig->render('article.html.twig', [
                'article' => $article,
                'authors' => isset($article->review) ? $this->authorRepo->findAuthorsOf($article->review['playable_id']) : ['a'],
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}