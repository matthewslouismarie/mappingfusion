<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\ArticleRepository;
use MF\Repository\AuthorRepository;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProfileController implements IController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private AuthorRepository $authorRepository,
        private PageFactory $pageFactory,
        private PlayableRepository $playableRepository,
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
        $author = $this->authorRepository->find($routeParams[0]);

        if (null === $author) {
            throw new RequestedResourceNotFound();
        }

        $articles = $this->articleRepository->findArticlesFrom($author['id']);

        return $this->twig->respond(
            'author.html.twig',
            $this->getPage($author),
            [
                'articles' => $articles,
                'author' => $author,
                'playables' => $this->playableRepository->findFromAuthor($author['id']),
            ],
        );
    }

    /**
     * @todo Add list of authors page.
     */
    public function getPage(AppObject $author): Page
    {
        return $this->pageFactory->create(
            $author['name'],
            self::class,
            [$author['id']],
            parentFqcn: HomeController::class,
        );
    }
}