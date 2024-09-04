<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Session\SessionManager;
use MF\Model\ModelFactory;
use MF\Model\ReviewModelFactory;
use MF\Repository\ArticleRepository;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminReviewController implements IFormController
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private FormFactory $formFactory,
        private PageFactory $pageFactory,
        private PlayableRepository $playableRepo,
        private ReviewModelFactory $reviewModelFactory,
        private ReviewRepository $repo,
        private Router $router,
        private TwigService $twig,
        private FormRequestHandler $formRequestHandler,
        private ModelFactory $modelFactory,
        private SessionManager $sessionManager,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $requestedId = $routeParams[1] ?? null;

        return $this->formRequestHandler->respondToRequest($this->repo, $request, $this, $requestedId);
    }

    public function getAccessControl(): Clearance
    {
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
        } else {
            return $this->pageFactory->create(
                name: "Test de {$review->playable->name}",
                controllerFqcn: self::class,
                controllerParams: [$review->id],
                parentFqcn: HomeController::class,
            );
        }
    }

    
    /**
     * @todo Why is it useless?
     */
    public function getFormConfig(): array
    {
        return [
            'id' => [
                'ignore' => true,
            ],
            'playable' => [
                'ignore' => true,
            ],
        ];
    }

    public function getFormModel(): IModel
    {
        return $this->modelFactory->getReviewModel();
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        $this->repo->delete($entityId);
        $this->sessionManager->addMessage('Le test a bien été supprimé.');
        return $this->router->redirect(AdminReviewListController::class);
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        $id = $this->repo->add($entity);
        $this->sessionManager->addMessage('Le nouveau test a bien été enregistré.');
        return $this->router->redirect(self::class, [$id]);
    }

    public function respondToUpdate(AppObject $entity, string $previousId): ResponseInterface
    {
        $this->repo->update($entity->removeProperty('playable'), $previousId);
        $this->sessionManager->addMessage('Le test a bien été mis à jour.');
        return $this->router->redirect(self::class, [$entity['id']]);
    }

    public function respondToNonPersistedRequest(
        ?array $formData,
        ?array $formErrors,
        ?array $deleteFormErrors,
        ?string $id,
    ): ResponseInterface {
        // var_dump($formErrors);
        return $this->twig->respond(
            'admin_review_form.html.twig',
            $this->getPage(is_null($id) ? null : new AppObject($formData)),
            [
                'formData' => $formData,
                'formErrors' => $formErrors,
                'playables' => $this->playableRepo->findAll(),
                'availableArticles' => $this->articleRepo->findArticlesWithNoReview(),
            ],
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Erreur lors de l’enregistrement du test.';
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        $id = explode('/', $request->getQueryParams()['route_params'])[1] ?? null;
        $formData['id'] = (int) $id;
        $formData['playable'] = $this->playableRepo->find($formData['playable_id']);
        return $formData;
    }
}