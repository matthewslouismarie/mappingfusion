<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements ControllerInterface
{
    const ROUTE_ID = 'view_article';

    public function __construct(
        private ArticleRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $article = $this->repo->findOne($request->getQueryParams()['id']);
        return new Response(
            body: $this->twig->render('article.html.twig', [
                'article' => $article,
            ]),
        );
    }

    public function getAccessControl(): int {
        return 0;
    }
}