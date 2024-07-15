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
use MF\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookController implements ControllerInterface
{
    public function __construct(
        private BookRepository $bookRepository,
        private FormController $formController,
        private PageFactory $pageFactory,
        private Router $router,
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
            model: new BookModel(new ChapterModel()),
            repository: $this->bookRepository,
            page: $this->getPage($book),
            request: $request,
            getSuccessfulRedirect: function ($appObject) {
                return $this->router->redirect(self::class, [$appObject['id']]);
            },
            twigFilename: 'admin_book.html.twig',
            entity: $book,
            id: $routeParams[1] ?? null,
            redirectAfterDeletion: $this->router->getUrl('AdminBookListController'),
            formConfig: [
                'id' => [
                    'required' => false,
                    'default' => function ($values) {
                        return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                    }
                ]
            ],
            idAlreadyTakenMessage: 'Il existe déjà un tutoriel avec le même ID.',
            successfulInsertMessage: 'Le tutoriel a été créé avec succès.',
            successfulUpdateMessage: 'Le tutoriel a été mis à jour avec succès.',
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