<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\BookRepository;
use MF\Repository\ChapterRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminChapterListController implements IController
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
        $book = $this->bookRepository->findOne($routeParams[0]);
        return $this->twig->respond(
            'admin_chapter_list.html.twig',
            $this->getPage($book),
            [
                'book' => $book,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(AppObject $book): Page
    {
        return $this->pageFactory->createPage(
            'Liste des chapitres',
            self::class,
            controllerParams: [$book['id']],
            parentFqcn: AdminBookController::class,
            parentControllerParams: $book,
            isIndexed: false,
        );
    }
}