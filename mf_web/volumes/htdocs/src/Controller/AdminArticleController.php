<?php

namespace MF\Controller;

use MF\Entity\DbEntityManager;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Form;
use MF\Form\FormManager;
use MF\Form\FormObjectManager;
use MF\Model\ArticleDefinition;
use MF\Repository\ArticleRepository;
use MF\Http\SessionManager;
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
        private ArticleDefinition $articleDefinition,
        private ArticleRepository $repo,
        private CategoryRepository $catRepo,
        private DbEntityManager $em,
        private Form $form,
        private FormManager $formManager,
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

        $form = $this->formManager->createFormDefinition(
            $this->articleDefinition,
            formConfig: ['cover_filename' => ['required' => null === $article]],
        );

        $formData = null;
        if ('POST' === $request->getMethod()) {
            $formData = $form->extractSubmittedValue($request);

            if (0 === count($formData->getErrors())) {
                $entityValue = $this->fOManager->toAppArray($formData, $this->articleDefinition, $article);
    
                try {
                    if (null === $article) {
                        $this->repo->addNewArticle($this->em->toAppArray($entityValue, $this->articleDefinition));
                        return $this->router->generateRedirect(self::ROUTE_ID, [$entityValue['id']]);
                    } else {
                        $this->repo->updateArticle($article['id'], $entityValue);
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
            $formData = $form->generateFormValue($article ?? []);
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