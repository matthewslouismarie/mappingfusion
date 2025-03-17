<?php

namespace MF\Controller;

use BadMethodCallException;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Model\Type\IModel;
use MF\Model\ChapterIndexModelFactory;
use MF\Model\ModelFactory;
use MF\Repository\ArticleRepository;
use MF\Repository\BookRepository;
use MF\Repository\ChapterIndexRepository;
use MF\Repository\ChapterRepository;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\RuntimeError;

class AdminChapterIndexCreationController implements IController, IFormController
{
    private AppObject $chapter;
    private array $articles;

    public function __construct(
        private ArticleRepository $articleRepository,
        private BookRepository $bookRepository,
        private ChapterIndexModelFactory $chapterIndexModelFactory,
        private ChapterIndexRepository $chapterIndexRepository,
        private ChapterRepository $chapterRepository,
        private FormRequestHandler $formController,
        private ModelFactory $modelFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twigService,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $chapterId = $routeParams[1];
        
        try {
            $this->chapter = $this->chapterRepository->findOne($chapterId);
        } catch (EntityNotFoundException $e) {
            throw new RequestedResourceNotFound(previous: $e);
        }

        $this->articles = $this->articleRepository->findFreeArticles();
        return $this->formController->respondToRequest($this->chapterIndexRepository, $request, $this);
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            "Ajouter un article au chapitre {$this->chapter['title']}",
            self::class,
            [
                $this->chapter['id'],
            ],
            AdminChapterController::class,
            function (AdminChapterController $controller) {
                return $controller->getPage($this->chapter['book'], $this->chapter);
            },
            false,
        );
    }

    
    public function getFormConfig(): array
    {
        return [
            'id' => [
                'ignore' => true,
            ],
            'chapter_id' => [
                'ignore' => true,
            ],
        ];
    }

    public function getFormModel(): IModel
    {
        return $this->chapterIndexModelFactory->create(isNew: true);
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        throw new BadMethodCallException();
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        $chapterIndexId = $this->chapterIndexRepository->add($entity->removeProperty('id'));
        return $this->router->redirect(AdminChapterIndexUpdateController::class, [$chapterIndexId]);
    }

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface
    {
        throw new BadMethodCallException();
    }

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface
    {
        return $this->twigService->respond(
            'admin_chapter_index_form.html.twig',
            $this->getPage(),
            [
                'free_articles' => $this->articles,
                'chapter' => $this->chapter,
                'formData' => $formData,
                'formErrors' => $formErrors,
                'deleteFormErrors' => $deleteFormErrors,
            ],
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Cet article fait déjà partie du chapitre.';
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        $formData['id'] = null;
        $formData['chapter_id'] = $this->chapter['id'];
        return $formData;
    }
}