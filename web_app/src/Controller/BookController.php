<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\BookRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class BookController implements IController
{
    public function __construct(
        private BookRepository $bookRepository,
        private TwigService $twigService,
        private PageFactory $pageFactory,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $book = $this->bookRepository->find($routeParams[0]);
        if (null === $book) {
            throw new RequestedResourceNotFound();
        }
        return $this->twigService->respond('book.html.twig', $this->getPage($book), ['book' => $book]);
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ALL;
    }

    public function getPage(AppObject $book): Page
    {
        return $this->pageFactory->create($book['title'], self::class, [$book['id']], HomeController::class);
    }
}