<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\ChapterRepository;
use MF\Repository\Exception\EntityNotFoundException;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminChapterArticlesController implements IController
{
    public function __construct(
        private ChapterRepository $chapterRepository,
        private PageFactory $pageFactory,
        private TwigService $twigService,
    ) {
    }
    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        $id = $routeParams[1];
        try {
            $chapter = $this->chapterRepository->findOne($id);
        } catch (EntityNotFoundException $e) {
            throw new RequestedResourceNotFound('The requested chapter could not be found.');
        }

        return $this->twigService->respond(
            'admin_chapter_articles.html.twig',
            $this->getPage($chapter),
            [
                'chapter' => $chapter,
            ],
        );
    }

    public function getPage(AppObject $entity): Page
    {
        return $this->pageFactory->create(
            "Articles de {$entity['title']}",
            self::class,
            [
                $entity['id'],
            ],
            AdminChapterController::class,
            fn (AdminChapterController $parentController) => $parentController->getPage($entity['book'], $entity),
            $entity['id'],
            false,
        );
    }
}