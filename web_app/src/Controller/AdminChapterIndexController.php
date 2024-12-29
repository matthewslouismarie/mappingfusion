<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
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
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Error\RuntimeError;

class AdminChapterIndexController implements IController, IFormController
{
    private AppObject $chapter;
    private array $articles;

    public function __construct(
        private ArticleRepository $articleRepository,
        private BookRepository $bookRepository,
        private ChapterIndexRepository $chapterIndexRepository,
        private ChapterRepository $chapterRepository,
        private FormRequestHandler $formController,
        private ModelFactory $modelFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twigService,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $chapterId = $routeParams[1];
        $this->chapter = $this->chapterRepository->find($chapterId);
        $this->articles = $this->articleRepository->findFreeArticles();

        // $article = $this->articleRepository->find($routeParams[1], onlyPublished: false);
        // $router = $this->router;

        // $formModel = (new ChapterIndexModelFactory(isNew: null === $article->chapterIndex))
        //     ->removeProperty('id')
        //     ->removeProperty('article_id');
        
        // return $this->formController->generateResponse(
        //     model: $formModel,
        //     repository: $this->chapterIndexRepository,
        //     page: $this->getPage($article, $article->chapterIndex),
        //     request: $request,
        //     getSuccessfulRedirect: function (AppObject $appObject) use ($article, $router) {
        //         return $router->redirect(self::class, [$article['id']]);
        //     },
        //     twigFilename: 'admin_chapter_index_form.html.twig',
        //     entity: $article->chapterIndex,
        //     id: $article->chapterIndex['id'] ?? null,
        //     redirectAfterDeletion: $this->router->getUrl('AdminArticleController', [$article['id']]),
        //     addBeforeCreateOrUpdate: $article->chapterIndex?->toArray() ?? [
        //         'article_id' => $article['id'],
        //     ],
        //     twigAdditionalParams: [
        //         'books' => $this->bookRepository->findAll(),
        //     ],
        //     idAlreadyTakenMessage: 'Une erreur est survenue.',
        //     successfulInsertMessage: 'L’article a bien été ajouté au chapitre.',
        //     successfulUpdateMessage: 'La position de l’article a bien été mise à jour.',
            
        // );
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
            [$this->chapter['id']],
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
        return $this->modelFactory->getChapterIndexModel(true);
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        return $this->router->redirect(AdminChapterArticlesController::class, [$this->chapter['id']]);
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        return $this->router->redirect(self::class, [$this->chapter['id']]);
    }

    public function respondToUpdate(AppObject $entity, string $persistedId): ResponseInterface
    {
        throw new RuntimeError("Should not happen.");
    }

    public function respondToNonPersistedRequest(ServerRequestInterface $request, ?array $formData, ?array $formErrors, ?array $deleteFormErrors): ResponseInterface
    {
        return $this->twigService->respond(
            'admin_chapter_index_form.html.twig',
            $this->getPage(),
            [
                'free_articles' => $this->articles,
                'entity' => $this->chapter,
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
        $formData['id'] = null;
        $formData['chapter_id'] = $this->chapter['id'];
        return $formData;
    }
}