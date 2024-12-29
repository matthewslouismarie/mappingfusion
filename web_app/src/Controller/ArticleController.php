<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements IController
{
    public function __construct(
        private ArticleRepository $repo,
        private AuthorRepository $authorRepo,
        private PageFactory $pageFactory,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        if (!key_exists(1, $routeParams)) {
            throw new RequestedResourceNotFound();
        }

        $article = $this->repo->find($routeParams[1], true, !$this->sessionManager->isUserLoggedIn());

        if (null === $article) {
            throw new RequestedResourceNotFound();
        }

        return new Response(
            body: $this->twig->render(
                'article.html.twig',
                $this->getPage($article),
                [
                    'article' => $article,
                    'relatedArticles' => $this->repo->findRelatedArticles($article),
                ],
            ),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }

    public function getPage(AppObject $article): Page
    {
        return $this->pageFactory->create(
            $article['title'],
            self::class,
            [$article['id']],
            ArticleListController::class,
            function (ArticleListController $parentController) use ($article) {
                return $parentController->getPage($article['category']);
            },
        );
    }
}