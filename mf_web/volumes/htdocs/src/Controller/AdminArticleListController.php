<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use MF\Repository\ArticleRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleListController implements ControllerInterface
{
    private Page $page;

    public function __construct(
        private ArticleRepository $repo,
        private PageFactory $pageFactory,
        private TwigService $twig,

    ) {
        $this->page = $this->pageFactory->createPage(
            'Liste des articles',
            self::class,
        );
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_article_list.html.twig', [
            'articles' => $this->repo->findAll(false),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page {
        return $this->page;
    }
}