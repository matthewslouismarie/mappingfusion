<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use MF\Repository\BookRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookListController implements ControllerInterface
{
    private Page $page;

    public function __construct(
        private BookRepository $bookRepository,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
        $this->page = $pageFactory->createPage(
            'Liste des tutoriels',
            self::class,
            parentFqcn: AdminArticleListController::class,
        );
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        $books = $this->bookRepository->findAll();
        return new Response(
            body: $this->twig->render('admin_book_list.html.twig', [
                'books' => $books,
                'page' => $this->page,
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page {
        return $this->page;
    }
}