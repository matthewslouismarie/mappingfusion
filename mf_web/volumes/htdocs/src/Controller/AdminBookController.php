<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\DataStructures\Slug;
use MF\Model\BookModel;
use MF\Model\ChapterModel;
use MF\Repository\BookRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookController implements ControllerInterface
{
    public function __construct(
        private BookRepository $bookRepository,
        private FormController $formController,
        private PageFactory $pageFactory,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        // @todo Use model to check.
        if (1 !== count($routeParams) && 2 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }

        return $this->formController->generateResponse(
            $request,
            $routeParams,
            $routeParams[1] ?? null,
            new BookModel(new ChapterModel()),
            [
                'id' => [
                    'required' => false,
                    'default' => function ($values) {
                        return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                    }
                ]
            ],
            'Il existe déjà un tutoriel avec le même ID.',
            $this->bookRepository,
            'admin_book.html.twig',
            function ($formData) {
                return null === $formData ? 'Nouveau tutoriel' : $formData['title'];
            },
            'Le tutoriel a été créé avec succès.',
            'Le tutoriel a été mis à jour avec succès.',
            true,
            page: $this->getPage($routeParams),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(array $pageParams): Page {
        $pageName = 'Nouveau tutoriel';
        if (isset($pageParams[1])) {
            $book = $this->bookRepository->find($pageParams[1]);
            if (null !== $book) {
                $pageName = "Gestion de {$book->title}";
            }
        }
        return $this->pageFactory->createPage(
            $pageName,
            self::class,
            parentFqcn: AdminBookListController::class,
            isIndexed: false,
        );
    }
}