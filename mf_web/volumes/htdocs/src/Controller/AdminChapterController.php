<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use MF\Model\BookModel;
use MF\Model\ChapterModel;
use MF\Repository\BookRepository;
use MF\Repository\ChapterRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminChapterController implements ControllerInterface
{
    public function __construct(
        private BookRepository $bookRepository,
        private ChapterRepository $chapterRepository,
        private FormController $formController,
        private PageFactory $pageFactory,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        // @todo RequestHandler should check this.
        if (2 !== count($routeParams) && 3 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }
        
        if (isset($routeParams[2])) {
            $chapter = $this->chapterRepository->find($routeParams[2]);
            $book = $chapter->book;
        }
        else {
            $chapter = null;
            $book = $this->bookRepository->find($routeParams[1]);
        }

        return $this->formController->generateResponse(
            $routeParams[2] ?? null,
            [
                'id' => [
                    'required' => false,
                    'default' => function ($values) {
                        return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                    }
                ],
            ],
            $routeParams,
            new ChapterModel(
                new AbstractEntity([
                    'id' => new SlugModel(),
                    'title' => new StringModel(),
                ]),
            ),
            $this->chapterRepository,
            $this->getPage($book, $chapter),
            $request,
            'Il existe déjà un chapitre avec le même ID, ou avec cet ordre.',
            'Le chapitre a été créé avec succès.',
            'Le chapitre a été mis à jour avec succès.',
            'admin_chapter.html.twig',
            [
                'book' => $book,
            ],
            true,
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(AppObject $book, ?AppObject $chapter): Page
    {
        if (null === $chapter) {
            return $this->pageFactory->create(
                name: 'Nouveau chapitre',
                controllerFqcn: self::class,
                parentFqcn: AdminBookController::class,
                getParent: function (AdminBookController $parentController) use ($book) {
                    return $parentController->getPage($book);
                },
                isIndexed: false,
            );
        }
        else {
            return $this->pageFactory->create(
                name: $chapter->title,
                controllerFqcn: self::class,
                controllerParams: [$chapter['id']],
                parentFqcn: AdminBookController::class,
                getParent: function (AdminBookController $adminBookController) use ($book) {
                    return $adminBookController->getPage($book);
                },
                isIndexed: false,
            );
        }
    }
}