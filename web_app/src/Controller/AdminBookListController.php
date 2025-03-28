<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\BookRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookListController implements IController, SinglePageOwner
{
    public function __construct(
        private BookRepository $bookRepository,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $books = $this->bookRepository->findAll();
        return $this->twig->respond(
            'admin_book_list.html.twig',
            $this->getPage(),
            [
                'books' => $books,
            ],
        );
    }

    public function getPage(): Page
    {
        return $this->pageFactory->createPage(
            name: 'Gestion des livres',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}