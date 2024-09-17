<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
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

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page {
        return $this->pageFactory->createPage(
            'Liste des tutoriels',
            self::class,
            parentFqcn: AdminArticleListController::class,
            isIndexed: false,
        );
    }
}