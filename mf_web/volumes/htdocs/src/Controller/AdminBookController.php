<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
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
        /**
         * @todo Use model to check.
         * @todo Should check this in every controller.
         */ 
        if (1 !== count($routeParams) && 2 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }

        $book = isset($routeParams[1]) ? $this->bookRepository->find($routeParams[1]) : null;

        return $this->formController->generateResponse(
            $routeParams[1],
            [
                'id' => [
                    'required' => false,
                    'default' => function ($values) {
                        return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                    }
                ]
            ],
            $routeParams,
            new BookModel(new ChapterModel()),
            $this->bookRepository,
            $this->getPage($book),
            $request,
            'Il existe déjà un tutoriel avec le même ID.',
            'Le tutoriel a été créé avec succès.',
            'Le tutoriel a été mis à jour avec succès.',
            'admin_book.html.twig',
            [],
            true,
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $book): Page {
        $pageName = null === $book ? 'Nouveau tutoriel' : "Gestion de {$book->title}";
        $params = null === $book ? [] : [$book->id];
        return $this->pageFactory->createPage(
            name: $pageName,
            controllerFqcn: self::class,
            controllerParams: $params,
            parentFqcn: AdminBookListController::class,
            isIndexed: false,
        );
    }
}