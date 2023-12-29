<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleController implements ControllerInterface
{
    public function __construct(
        private ArticleRepository $repo,
        private AuthorRepository $authorRepo,
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
            body: $this->twig->render('article.html.twig', [
                'article' => $article,
                'relatedArticles' => $this->repo->findRelatedArticles($article),
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}