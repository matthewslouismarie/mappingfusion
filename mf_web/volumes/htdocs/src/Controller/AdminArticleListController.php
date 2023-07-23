<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleListController implements ControllerInterface
{
    const ROUTE_ID = 'admin-article-list';

    private ArticleRepository $repo;

    private TwigService $twig;

    public function __construct(
        ArticleRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_article_list.html.twig', [
            'articles' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}