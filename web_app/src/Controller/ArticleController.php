<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\BookRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements IController
{
    public function __construct(
        private ArticleRepository $repo,
        private AuthorRepository $authorRepo,
        private BookRepository $bookRepository,
        private PageFactory $pageFactory,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        if (!key_exists(0, $routeParams)) {
            throw new RequestedResourceNotFound();
        }

        $article = $this->repo->find($routeParams[0], true, !$this->sessionManager->isUserLoggedIn());

        if (null === $article) {
            throw new RequestedResourceNotFound();
        }

        $book = null === $article['chapter_index'] ? null : $this->bookRepository->findOne($article['chapter_index']['chapter']['book_id']);

        return new Response(
            body: $this->twig->render(
                'article.html.twig',
                $this->getPage($article),
                [
                    'article' => $article,
                    'book' => $book,
                    'relatedArticles' => $this->repo->findRelatedArticles($article),
                ],
            ),
        );
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