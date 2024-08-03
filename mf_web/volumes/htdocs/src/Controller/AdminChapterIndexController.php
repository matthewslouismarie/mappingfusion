<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Model\ChapterIndexModelFactory;
use MF\Repository\ArticleRepository;
use MF\Repository\BookRepository;
use MF\Repository\ChapterIndexRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminChapterIndexController implements ControllerInterface
{
    public function __construct(
        private BookRepository $bookRepository,
        private ChapterIndexRepository $chapterIndexRepository,
        private FormController $formController,
        private PageFactory $pageFactory,
        private TwigService $twigService,
        private ArticleRepository $articleRepository,
        private Router $router,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $article = $this->articleRepository->find($routeParams[1], onlyPublished: false);
        $router = $this->router;

        $formModel = (new ChapterIndexModelFactory(isNew: null === $article->chapterIndex))
            ->removeProperty('id')
            ->removeProperty('article_id');
        
        return $this->formController->generateResponse(
            model: $formModel,
            repository: $this->chapterIndexRepository,
            page: $this->getPage($article, $article->chapterIndex),
            request: $request,
            getSuccessfulRedirect: function (AppObject $appObject) use ($article, $router) {
                return $router->redirect(self::class, [$article->id]);
            },
            twigFilename: 'admin_chapter_index_form.html.twig',
            entity: $article->chapterIndex,
            id: $article->chapterIndex?->id,
            redirectAfterDeletion: $this->router->getUrl('AdminArticleController', [$article->id]),
            addBeforeCreateOrUpdate: $article->chapterIndex?->toArray() ?? [
                'article_id' => $article->id,
            ],
            twigAdditionalParams: [
                'books' => $this->bookRepository->findAll(),
            ],
            idAlreadyTakenMessage: 'Une erreur est survenue.',
            successfulInsertMessage: 'L’article a bien été ajouté au chapitre.',
            successfulUpdateMessage: 'La position de l’article a bien été mise à jour.',
            
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(AppObject $article, ?AppObject $chapterIndex): Page
    {
        return $this->pageFactory->create(
            null === $chapterIndex ? "Ajouter « {$article['title']} » à un chapitre" : "Position de « {$article['title']} » dans le chapitre",
            self::class,
            null === $chapterIndex ? [$article['id']] : [$article['id'], $chapterIndex['id']],
            AdminArticleController::class,
            function (AdminArticleController $controller) use ($article) {
                return $controller->getPage($article);
            },
            false,
        );
    }
}