<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\KeyName;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Session\SessionManager;
use MF\Model\BookModelFactory;
use MF\Model\ChapterModelFactory;
use MF\Model\ModelFactory;
use MF\Repository\BookRepository;
use MF\Router;
use MF\Twig\TemplateHelper;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminBookController implements IFormController
{
    public function __construct(
        private BookRepository $bookRepository,
        private FormRequestHandler $formController,
        private ModelFactory $modelFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private SessionManager $sessionManager,
        private TwigService $twigService,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $requestedId = $routeParams[0] ?? null;

        return $this->formController->respondToRequest($this->bookRepository, $request, $this, $requestedId);
    }

    public function getPage(?AppObject $book): Page
    {
        $pageName = null === $book ? 'Nouveau tutoriel' : "Gestion de {$book['title']}";
        $params = null === $book ? [] : [$book['id']];
        return $this->pageFactory->createPage(
            name: $pageName,
            controllerFqcn: self::class,
            controllerParams: $params,
            parentFqcn: AdminBookListController::class,
            isIndexed: false,
        );
    }

    
    public function getFormConfig(): array
    {
        return [
            'id' => [
                'required' => false,
                'default' => function ($values) {
                    return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                }
            ]
        ];
    }

    public function getFormModel(): IModel
    {
        return $this->modelFactory->getBookModel();
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        $this->bookRepository->delete($entityId);
        return $this->router->redirect(AdminBookListController::class);
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        $this->bookRepository->add($entity);
        $this->sessionManager->addMessage('Le tutoriel a été créé avec succès.');
        return $this->router->redirect(AdminBookController::class, [$entity['id']]);
    }

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface
    {
        $this->bookRepository->update($entity, $persistedId);
        $this->sessionManager->addMessage('Le tutoriel a été mis à jour avec succès.');
        return $this->router->redirect(AdminBookController::class, [$entity['id']]);
    }

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface
    {
        $id = $this->router->getRouteParams($request)[1] ?? null;
        return $this->twigService->respond(
            'admin_book.html.twig',
            $this->getPage(null === $id ? null : new AppObject($formData)),
            [
                'requestedId' => $id,
                'formData' => $formData,
                'formErrors' => $formErrors,
            ]
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Il existe déjà un tutoriel avec le même identifiant.';
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        if (null === $formData['id'] && null !== $formData['title']) {
            $formData['id'] = (new Slug($formData['title'], true))->__toString();
        }
        return $formData;
    }
}