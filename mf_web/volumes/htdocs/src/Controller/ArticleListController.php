<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleListController implements ControllerInterface
{
    const ROUTE_ID = 'articles';

    public function __construct(
        private ArticleRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(
            body: $this->twig->render('article_list.html.twig', [
                'articles' => $this->repo->findAll(),
            ])
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}