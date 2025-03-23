<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorAlreadyLoggedInController implements IController
{
    public function __construct(
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return $this->twig->respond(
            'errors/error_page.html.twig',
            $this->getPage($request),
            [
                'message' => 'Tu es déjà connecté.',
            ],
        );
    }

    public function getPage(ServerRequestInterface $request): Page
    {
        $path = $this->router->getRequestUrl($request);
        return new Page(
            parent: null,
            name: 'Tu es déjà connecté.',
            url: $path,
            isIndexed: false,
            isPartOfHierarchy: false,
        );
    }
}