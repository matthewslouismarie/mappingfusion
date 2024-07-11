<?php

namespace MF\Controller;

use LM\WebFramework\Controller\ResponseGenerator;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorAlreadyLoggedInController implements ResponseGenerator, SinglePageOwner
{
    public function __construct(
        private TwigService $twig,
        private PageFactory $pageFactory,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return $this->twig->respond(
            'errors/error_page.html.twig',
            $this->getPage(),
            [
                'message' => 'Tu es déjà connecté.',
            ],
        );
    }

    function getPage(): Page
    {
        return $this->pageFactory->create(
            'Tu es déjà connecté',
            self::class,
            isIndexed: false,
            partOfHierarchy: false,
        );
    }
}