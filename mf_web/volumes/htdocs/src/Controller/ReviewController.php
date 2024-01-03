<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
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

        return new Response(body: $this->twig->render('admin_review_form.html.twig', [
            'formData' => $formData,
            'formErrors' => $formErrors,
            'playables' => $this->playableRepo->findAll(),
            'availableArticles' => $this->articleRepo->findAvailableArticles(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}