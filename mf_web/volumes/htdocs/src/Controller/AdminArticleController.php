<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validator\ModelValidator;
use MF\Model\ArticleModelFactory;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\Type\EntityModel;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Repository\ArticleRepository;
use MF\Repository\BookRepository;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\Twig\TemplateHelper;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleController implements ControllerInterface
{
    private EntityModel $model;

    public function __construct(
        private ArticleModelFactory $articleModelFactory,
        private ArticleRepository $repo,
        private BookRepository $bookRepository,
        private CategoryRepository $catRepo,
        private DbEntityManager $em,
        private FormController $formController,
        private FormFactory $formFactory,
        private PageFactory $pageFactory,
        private Router $router,
        private SessionManager $session,
        private TemplateHelper $templateHelper,
        private TwigService $twig,
    ) {
        $this->model = $articleModelFactory
            ->create(chapterId: true)
            ->removeProperty('creation_date_time')
            ->removeProperty('last_update_date_time')
        ;
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $formData = null;
        $formErrors = null;
        $lastUpdateDateTimeUtc = null;
        $requestedId = $routeParams[1] ?? null;
        $requestedEntity = null;
        $books = $this->bookRepository->findAll();

        $form = $this->formFactory->createForm(
            $this->model,
            config: [
                'cover_filename' => [
                    'required' => null === $requestedId,
                ],
                'writer_id' => [
                    'ignore' => true,
                ],
            ],
        )
        ;

        if ('POST' === $request->getMethod()) {
            $lastUpdateDateTimeUtc = time() * 1000;
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] ?? (null !== $formData['title'] ? (new Slug($formData['title'], true))->__toString() : null);
            $formData['writer_id'] = $this->session->getCurrentMemberUsername();
            $validator = new ModelValidator($this->model);
            $formErrors = $validator->validate($formData);
    
            if (0 === count($formErrors)) {
                return $this->persistPostData($request, $formData, $requestedId);
            }
        }
        
        if (null !== $requestedId) {
            try {
                $requestedEntity = $this->repo->findOne($requestedId);
            } catch (EntityNotFoundException $e) {
                throw new RequestedResourceNotFound(previous: $e);
            }

            if (null === $formData) {
                $formData = $requestedEntity->toArray();
                $lastUpdateDateTimeUtc = $formData['last_update_date_time']->getTimestamp() * 1000;
            }
        }

        return $this->twig->respond(
            'admin_article_form.html.twig',
            $this->getPage($requestedEntity),
            [
                'categories' => $this->catRepo->findAll(),
                'books' => $books,
                'formData' => $formData,
                'formErrors' => $formErrors,
                'lastUpdateDateTimeUtc' => $lastUpdateDateTimeUtc,
                'requestedId' => $requestedId,
            ],
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $article): Page {
        /**
         * @todo Create function to remove any quotation mark (", ', «…)
         */
        $pageName = (null === $article) ? 'Nouvel article' : "Édition de \"{$article->title}\"";

        return $this->pageFactory->createPage(
            $pageName,
            self::class,
            null === $pageName ? [] : [$article['id']],
            AdminArticleListController::class,
            isIndexed: false,
        );
    }

    /**
     * @param mixed[] $formData The prepared, validated form data.
     * @param ?string $requestedId The ID of the article, if it already exists
     * in the database.
     */
    private function persistPostData(
        ServerRequestInterface $request,
        array $formData,
        ?string $requestedId,
    ): ResponseInterface {
        $article = new AppObject($formData);
        if (null === $requestedId) {
            $this->repo->add($article);
        } else {
            if ($this->formController->isCheckboxChecked($request, 'update_author')) {
                $this->session->addMessage('Article mis à jour et auteur modifié.');
                $this->repo->update($article, $requestedId, true);
            } else {
                $this->session->addMessage('Article mis à jour.');
                $this->repo->update($article, $requestedId);
            }
        }
        return $this->router->generateRedirect('manage-article', [$article->id]);
    }
}