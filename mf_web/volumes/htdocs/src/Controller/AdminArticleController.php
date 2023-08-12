<?php

namespace MF\Controller;

use DateTimeImmutable;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Form\FormFactory;
use MF\Form\FormObjectManager;
use MF\Model\ArticleModel;
use MF\Model\Slug;
use MF\Repository\ArticleRepository;
use MF\Session\SessionManager;
use GuzzleHttp\Psr7\Response;
use MF\Repository\CategoryRepository;
use MF\Router;
use MF\Twig\TemplateHelper;
use MF\TwigService;
use OutOfBoundsException;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminArticleController implements ControllerInterface
{
    const ROUTE_ID = 'manage-article';

    public function __construct(
        private AppObjectFactory $appObjectFactory,
        private ArticleModel $articleModel,
        private ArticleRepository $repo,
        private CategoryRepository $catRepo,
        private DbEntityManager $em,
        private FormFactory $formFactory,
        private FormObjectManager $fOManager,
        private Router $router,
        private SessionManager $session,
        private TemplateHelper $templateHelper,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $article = null;
        $submission = null;
        $processingErrors = [];

        try {
            $article = isset($routeParams[1]) ? $this->repo->findOne($routeParams[1]) : null;
        } catch (OutOfBoundsException $e) {
            throw new NotFoundException($e);
        }

        $form = $this->formFactory->createForm(
            $this->articleModel,
            formConfig: ['cover_filename' => ['required' => null === $article]],
        );

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFormData($request);
            if (!$submission->hasErrors()) {

                $formData = $submission->getData();

                $formData['id'] = $article->id ?? (new Slug($formData['title'], true))->__toString();
                $formData['author_id'] = $this->session->getCurrentMemberUsername();
                $formData['cover_filename'] = $formData['cover_filename'] ?? $article->coverFilename;
                $formData['creation_date_time'] = $article->creationDateTime ?? new DateTimeImmutable();
                $formData['last_update_date_time'] = new DateTimeImmutable();

                // $appObject = $this->appObjectFactory->create($formData, $this->articleModel);
    
                try {
                    if (null === $article) {
                        $this->repo->add($formData);
                        return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                    } else {
                        $this->repo->updateArticle($article->id, $formData);
                        if ($article->id !== $formData['id']) {
                            return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                        }
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $processingErrors[] = 'Il existe déjà un article avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        } else {
            $submission = $form->generateSubmission($article, false);
        }

        return new Response(body: $this->twig->render('article_form.html.twig', [
            'categories' => $this->catRepo->findAll(),
            'processingErrors' => $processingErrors,
            'submission' => $submission,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}