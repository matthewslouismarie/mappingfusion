<?php

namespace MF\Controller;

use BadMethodCallException;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\AppList;
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

class AdminChapterIndexUpdateController implements IController, IFormController
{
    private AppList $articles;
    private AppObject $chapterIndex;

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
        $chapterIndexId = $routeParams[0];
        
        try {
            $this->chapterIndex = $this->chapterIndexRepository->findOne($chapterIndexId);
        } catch (EntityNotFoundException $e) {
            throw new RequestedResourceNotFound(previous: $e);
        }

        $this->articles = $this->articleRepository->findFreeArticles();
        return $this->formController->respondToRequest($this->chapterIndexRepository, $request, $this, $chapterIndexId);
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            "Modifier le contenu du chapitre {$this->chapterIndex['chapter']['title']}",
            self::class,
            [
                $this->chapterIndex['chapter']['id'],
            ],
            AdminChapterArticlesController::class,
            function (AdminChapterArticlesController $controller) {
                return $controller->getPage($this->chapterIndex['chapter']);
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
        return $this->chapterIndexModelFactory->create();
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        return $this->router->redirect(AdminChapterArticlesController::class, [$this->chapterIndex['chapter']['id']]);
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        throw new BadMethodCallException("Method not supported.");
    }

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface
    {
        $this->chapterIndexRepository->update($entity);
        return $this->router->redirect(self::class, [$entity['id']]);
    }

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface
    {
        return $this->twigService->respond(
            'admin_chapter_index_form.html.twig',
            $this->getPage(),
            [
                'free_articles' => $this->articles,
                'chapter' => $this->chapterIndex['chapter'],
                'chapter_index' => $this->chapterIndex,
                'formData' => $formData,
                'formErrors' => $formErrors,
                'deleteFormErrors' => $deleteFormErrors,
            ],
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Existe déjà.';
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        $formData['id'] = $this->chapterIndex['id'];
        $formData['chapter_id'] = $this->chapterIndex['chapter']['id'];
        return $formData;
    }
}