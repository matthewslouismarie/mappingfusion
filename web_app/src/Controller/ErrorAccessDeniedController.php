<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\IResponseGenerator;
use LM\WebFramework\DataStructures\Page;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorAccessDeniedController implements IResponseGenerator
{
    public function __construct(
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        return $this->twig->respond(
            'errors/error_page.html.twig',
            $this->getPage($request),
            [
                'message' => 'Désolé monsieur Freeman, l’accès est interdit… ou alors il faut vous connecter.',
            ],
            403,
        );
    }

    public function getPage(ServerRequestInterface $request): Page
    {
        $path = $this->router->getRequestUrl($request);
        return new Page(
            parent: null,
            name: 'Accès interdit',
            url: $path,
            isIndexed: false,
            isPartOfHierarchy: false,
        );
    }
}