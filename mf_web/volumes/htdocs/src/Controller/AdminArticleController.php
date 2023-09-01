<?php

namespace MF\Controller;

use DateTimeImmutable;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Framework\Form\FormFactory;
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
        private Router $router,
        private SessionManager $session,
        private TemplateHelper $templateHelper,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $existingArticle = null;
        $submission = null;
        $formData = null;
        $formErrors = null;

        try {
            $existingArticle = isset($routeParams[1]) ? $this->repo->findOne($routeParams[1]) : null;
        } catch (OutOfBoundsException $e) {
            throw new NotFoundException($e);
        }

        $form = $this->formFactory->createForm(
            $this->articleModel,
            formConfig: ['cover_filename' => ['required' => null === $existingArticle]],
        );

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formData = $submission->getContent();
            $formErrors = $submission->getErrors();

            if (!$submission->hasErrors()) {
                $formData['id'] = $existingArticle->id ?? (new Slug($formData['title'], true))->__toString();
                $formData['author_id'] = $this->session->getCurrentMemberUsername();
                $formData['creation_date_time'] = $existingArticle->creationDateTime ?? new DateTimeImmutable();
                $formData['last_update_date_time'] = new DateTimeImmutable();
                $article = $this->appObjectFactory->create($formData, $this->articleModel);

                try {
                    if (null === $existingArticle) {
                        $this->repo->add($article);
                        return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                    } else {
                        $this->repo->updateArticle($article, $existingArticle->id);
                        if ($article->id !== $formData['id']) {
                            return $this->router->generateRedirect(self::ROUTE_ID, [$formData['id']]);
                        }
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $formErrors['title'][] = 'Il existe déjà un article avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        } else {
            $formData = $existingArticle;
            $formErrors = null;
        }

        return new Response(body: $this->twig->render('article_form.html.twig', [
            'categories' => $this->catRepo->findAll(),
            'formData' => $formData,
            'formErrors' => $formErrors,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}