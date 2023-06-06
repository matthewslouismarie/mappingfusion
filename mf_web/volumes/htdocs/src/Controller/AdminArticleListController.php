<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleListController implements ControllerInterface
{
    const ROUTE_ID = 'admin_article_list';

    private ArticleRepository $repo;

    private TwigService $twig;

    public function __construct(
        ArticleRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_article_list.html.twig', [
            'articles' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): int {
        return 1;
    }
}