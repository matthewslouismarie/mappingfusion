<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\ReviewModel;
use MF\Repository\ArticleRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ReviewController implements ControllerInterface
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private FormFactory $formFactory,
        private ModelValidator $modelValidator,
        private PageFactory $pageFactory,
        private PlayableRepository $playableRepo,
        private ReviewModel $model,
        private ReviewRepository $repo,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $requestedId = $routeParams[1] ?? null;

        $formData = null;
        $formErrors = null;
        $form = $this->formFactory->createForm($this->model);

        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formErrors = $this->modelValidator->validate($formData, $this->model);

            if (0 === count($formErrors)) {
                $review = new AppObject($formData);
                if (null === $requestedId) {
                    $requestedId = $this->repo->add($review);
                } else {
                    $this->repo->update($review);
                }

                return $this->router->generateRedirect('gestion-de-tests', [$requestedId]);
            }
        } elseif (null !== $requestedId) {
            $formData = $this->repo->find($requestedId)?->toArray();
            if (null === $formData) {
                throw new RequestedResourceNotFound();
            }
        }

        return $this->twig->respond(
            'admin_review_form.html.twig',
            $this->getPage(is_null($requestedId) ? null : new AppObject($formData)),
            [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'playables' => $this->playableRepo->findAll(),
                'availableArticles' => $this->articleRepo->findAvailableArticles(),
            ],
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(?AppObject $review): Page
    {
        if (null === $review) {
            return $this->pageFactory->create(
                name: 'Nouveau test',
                controllerFqcn: self::class,
                parentFqcn: HomeController::class,
            );
        }
        else {
            return $this->pageFactory->create(
                name: "Test de {$review->playable_id}",
                controllerFqcn: self::class,
                controllerParams: [$review->id],
                parentFqcn: HomeController::class,
            );
        }
    }
}