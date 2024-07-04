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
    public function __construct(
        private BookRepository $bookRepository,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        $books = $this->bookRepository->findAll();
        return new Response(
            body: $this->twig->render('admin_book_list.html.twig', [
                'books' => $books,
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}