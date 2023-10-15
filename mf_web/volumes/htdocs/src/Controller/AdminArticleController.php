<?php

namespace MF\Controller;

use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructures\AppObject;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Framework\Form\FormFactory;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Type\ModelValidator;
use MF\Model\ArticleModel;
use MF\Model\Slug;
use MF\Repository\ArticleRepository;
use MF\Session\SessionManager;
use GuzzleHttp\Psr7\Response;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\Twig\TemplateHelper;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleController implements ControllerInterface
{
    const ROUTE_ID = 'manage-article';

    private AbstractEntity $model;

    public function __construct(
        private ArticleRepository $repo,
        private CategoryRepository $catRepo,
        private DbEntityManager $em,
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private Router $router,
        private SessionManager $session,
        private TemplateHelper $templateHelper,
        private TwigService $twig,
    ) {
        $this->model = (new ArticleModel())
            ->removeProperty('creation_date_time')
            ->removeProperty('last_update_date_time')
        ;
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $formData = null;
        $formErrors = null;
        $requestedId = $routeParams[1] ?? null;

        $form = $this->formFactory->createForm(
            $this->model,
            config: [
                'cover_filename' => [
                    'required' => null === $requestedId,
                ],
                'author_id' => [
                    'ignore' => true,
                ],
            ],
        )
        ;

        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] ?? (null !== $formData['title'] ? (new Slug($formData['title'], true))->__toString() : null);
            $formData['author_id'] = $this->session->getCurrentMemberUsername();

            $formErrors = $this->modelValidator->validate($formData, $this->model);
    
            if (0 === count($formErrors)) {
                $article = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->add($article);
                } else {
                    if (isset($request->getParsedBody()['update_author']) && 'on' === $request->getParsedBody()['update_author']) {
                        $this->session->addMessage('Article mis à jour et auteur modifié.');
                        $this->repo->updateArticle($article, $requestedId);
                    } else {
                        $this->session->addMessage('Article mis à jour.');
                        $this->repo->updateArticle($article, $requestedId, false);
                    }
                }
                return $this->router->generateRedirect(self::ROUTE_ID, [$article->id]);
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId, onlyPublished: false)?->toArray();
            if (null === $formData) {
                throw new NotFoundException();
            }
        }

        return new Response(body: $this->twig->render('admin_article_form.html.twig', [
            'categories' => $this->catRepo->findAll(),
            'formData' => $formData,
            'formErrors' => $formErrors,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}