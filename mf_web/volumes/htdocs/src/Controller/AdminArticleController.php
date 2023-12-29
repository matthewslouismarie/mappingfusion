<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\ArticleModel;
use MF\Model\Slug;
use MF\Repository\ArticleRepository;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\Twig\TemplateHelper;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleController implements ControllerInterface
{
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
        $lastUpdateDateTimeUtc = null;
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
            $lastUpdateDateTimeUtc = time() * 1000;
            $formErrors = $this->modelValidator->validate($formData, $this->model);
    
            if (0 === count($formErrors)) {
                $article = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->add($article);
                } else {
                    if (isset($request->getParsedBody()['update_author']) && 'on' === $request->getParsedBody()['update_author']) {
                        $this->session->addMessage('Article mis à jour et auteur modifié.');
                        $this->repo->updateArticle($article, $requestedId, true);
                    } else {
                        $this->session->addMessage('Article mis à jour.');
                        $this->repo->updateArticle($article, $requestedId);
                    }
                }
                return $this->router->generateRedirect(self::ROUTE_ID, [$article->id]);
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId, onlyPublished: false)?->toArray();
            $lastUpdateDateTime = $formData['last_update_date_time'];
            $lastUpdateDateTimeUtc = $lastUpdateDateTime->getTimestamp() * 1000;
        }

        return new Response(body: $this->twig->render('admin_article_form.html.twig', [
            'categories' => $this->catRepo->findAll(),
            'formData' => $formData,
            'formErrors' => $formErrors,
            'requestedId' => $requestedId,
            'lastUpdateDateTimeUtc' => $lastUpdateDateTimeUtc,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}