<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Exception\Http\NotFoundException;
use MF\Form\FormFactory;
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
    const ROUTE_ID = 'manage_review';

    public function __construct(
        private ArticleRepository $articleRepo,
        private PlayableRepository $playableRepo,
        private ReviewRepository $repo,
        private Router $router,
        private TwigService $twig,
        private FormFactory $formFactory,
        private ReviewModel $model,
        private AppObjectFactory $appObjectFactory,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $id = $routeParams[1] ?? null;
        $existingReview = null !== $id ? $this->repo->find($routeParams[1]) : null;

        if (null === $existingReview && key_exists(1, $routeParams)) {
            throw new NotFoundException();
        }

        $formData = $existingReview;
        $formErrors = null;
        $form = $this->formFactory->createForm($this->model);

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFromRequest($request->getParsedBody());
            $formData = $submission->getContent();
            $formErrors = $submission->getErrors();

            if (!$submission->hasErrors()) {
                $review = $this->appObjectFactory->create($formData, $this->model);
                if (null === $id) {
                    $id = $this->repo->add($review);
                } else {
                    $this->repo->update($review);
                }

                return $this->router->generateRedirect(self::ROUTE_ID, [$id]);
            }
        }

        return new Response(body: $this->twig->render('review_form.html.twig', [
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