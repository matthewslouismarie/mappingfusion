<?php

namespace MF\Controller;

use DateTimeImmutable;
use MF\Database\DbEntityManager;
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
        private ArticleModel $articleDefinition,
        private ArticleRepository $repo,
        private CategoryRepository $catRepo,
        private DbEntityManager $em,
        private FormFactory $FormFactory,
        private FormObjectManager $fOManager,
        private Router $router,
        private SessionManager $session,
        private TemplateHelper $templateHelper,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $article = null;
        $errorMessages = [];

        try {
            $article = isset($routeParams[1]) ? $this->repo->findOne($routeParams[1]) : null;
        } catch (OutOfBoundsException $e) {
            throw new NotFoundException($e);
        }

        $form = $this->FormFactory->createForm(
            $this->articleDefinition,
            formConfig: ['cover_filename' => ['required' => null === $article]],
        );

        $formData = null;
        if ('POST' === $request->getMethod()) {
            $formData = $form->extractFormData($request);
            if (!$formData->hasErrors()) {

                $data = $formData->getData();

                $data['id'] = $article->id ?? (new Slug($data['title'], true))->__toString();
                $data['author_id'] = $this->session->getCurrentMemberUsername();
                $data['cover_filename'] = $data['cover_filename'] ?? $article->coverFilename;
                $data['creation_date_time'] = $article->creationDateTime ?? new DateTimeImmutable();
                $data['last_update_date_time'] = new DateTimeImmutable();

                $appEntity = $this->fOManager->toAppObject($data, $this->articleDefinition);
    
                try {
                    if (null === $article) {
                        $this->repo->add($appEntity);
                        return $this->router->generateRedirect(self::ROUTE_ID, [$appEntity['id']]);
                    } else {
                        $this->repo->updateArticle($article->id, $appEntity);
                        if ($article->id !== $appEntity->id) {
                            return $this->router->generateRedirect(self::ROUTE_ID, [$appEntity['id']]);
                        }
                    }
                } catch (PDOException $e) {
                    if ('23000' === $e->getCode()) {
                        $errorMessages[] = 'Il existe déjà un article avec le même ID.';
                    } else {
                        throw $e;
                    }
                }
            }
        } else {
            $formData = $form->generateFormData($article?->toArray() ?? [], false);
        }

        return new Response(body: $this->twig->render('article_form.html.twig', [
            'categories' => $this->catRepo->findAll(),
            'article' => $formData,
            'errorMessages' => $errorMessages,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}