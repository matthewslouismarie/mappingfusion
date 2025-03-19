<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ModelFactory;
use MF\Repository\ArticleRepository;
use MF\Repository\BookRepository;
use MF\Repository\ChapterRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminChapterController implements IController, IFormController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        private BookRepository $bookRepository,
        private ChapterRepository $chapterRepository,
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
        // @todo RequestHandler should check this.
        if (2 !== count($routeParams) && 3 !== count($routeParams)) {
            throw new RequestedResourceNotFound();
        }

        list($book, $chapter) = $this->getBookAndChapter($request);

        // $freeArticles = 3 === count($routeParams) ? $this->articleRepository->findFreeArticles($routeParams[1]) : null;

        return $this->formController->respondToRequest($this->chapterRepository, $request, $this, $chapter['id'] ?? null);
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(AppObject $book, ?AppObject $chapter): Page
    {
        if (null === $chapter) {
            return $this->pageFactory->createPage(
                name: 'Nouveau chapitre',
                controllerFqcn: self::class,
                parentFqcn: AdminChapterListController::class,
                parentControllerParams: $book,
                isIndexed: false,
            );
        } else {
            return $this->pageFactory->createPage(
                name: $chapter['title'],
                controllerFqcn: self::class,
                controllerParams: [$chapter['book_id'], $chapter['id']],
                parentFqcn: AdminChapterListController::class,
                parentControllerParams: $book,
                isIndexed: false,
            );
        }
    }

    public function getFormConfig(): array
    {
        return [
            'id' => [
                'required' => false,
                'default' => function ($values) {
                    return null !== $values['title'] ? (new Slug($values['title'], true))->__toString() : null;
                }
            ],
            'book_id' => [
                'ignore' => true,   
            ]
        ];
    }

    public function getFormModel(): IModel
    {
        return $this->modelFactory->getChapterModel(false);
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        $bookId = $this->chapterRepository->find($entityId)['book_id'];
        $this->chapterRepository->delete($entityId);
        $this->sessionManager->addMessage('Le chapitre a bien été supprimé.');
        return $this->router->redirect(AdminBookController::class, [$bookId]);
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        $this->chapterRepository->add($entity);
        $this->sessionManager->addMessage('Le chapitre a été créé avec succès.');
        return $this->router->redirect(self::class, [$entity['book_id'], $entity['id']]);
    }

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface
    {
        $this->chapterRepository->update($entity, $persistedId);
        $this->sessionManager->addMessage('Le chapitre a été mis à jour avec succès.');
        return $this->router->redirect(self::class, [$entity['book_id'], $entity['id']]);
    }

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface
    {
        list($book, $chapter) = $this->getBookAndChapter($request);
        return $this->twigService->respond(
            'admin_chapter.html.twig',
            $this->getPage($book, $chapter),
            [
                'book' => $book,
                'chapter' => $chapter,
                'formData' => $formData,
                'formErrors' => $formErrors,
            ],
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Il existe déjà un chapitre avec le même ID, ou avec cet ordre.';
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        $formData['book_id'] = $this->router->getRouteParams($request)[1];
        if (null === $formData['id'] && null !== $formData['title']) {
            $formData['id'] = (new Slug($formData['title'], true, true))->__toString();
        }
        return $formData;
    }

    /**
     * @return [AppObject, AppObject]
     */
    private function getBookAndChapter(ServerRequestInterface $request): array
    {
        $routeParams = $this->router->getRouteParams($request);
        $chapterId = $routeParams[1] ?? null;
        
        if (null !== $chapterId) {
            $chapter = $this->chapterRepository->find($chapterId);
            $book = $chapter['book'];
        }
        else {
            $chapter = null;
            $book = $this->bookRepository->find($routeParams[0]);
        }

        return [$book, $chapter];
    }
}