<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController implements ControllerInterface
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private AuthorRepository $authorRepository,
        private PlayableRepository $playableRepository,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        if (!key_exists(1, $routeParams)) {
            throw new RequestedResourceNotFound();
        }
        $author = $this->authorRepository->find($routeParams[1]);

        if (null === $author) {
            throw new RequestedResourceNotFound();
        }

        $articles = null !== $author->member ? $this->articleRepository->findArticlesFrom($author->member->id) : null;

        return new Response(
            body: $this->twig->render('author.html.twig', [
                'articles' => $articles,
                'author' => $author,
                'playables' => $this->playableRepository->findFrom($author->id),
            ])
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}