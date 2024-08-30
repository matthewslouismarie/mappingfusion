<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use MF\Model\ContributionModelFactory;
use MF\Model\PlayableLinkModelFactory;
use MF\Model\PlayableModelFactory;
use LM\WebFramework\DataStructures\Slug;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\AuthorRepository;
use MF\Repository\PlayableRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPlayableController implements IFormController
{
    private EntityModel $formModel;
    
    public function __construct(
        private AuthorRepository $authorRepo,
        private ContributionModelFactory $contributionModelFactory,
        private FormRequestHandler $formRequestHandler,
        private PageFactory $pageFactory,
        private PlayableLinkModelFactory $playableLinkModelFactory,
        private PlayableModelFactory $playableModelFactory,
        private PlayableRepository $repo,
        private Router $router,
        private SessionManager $sessionManager,
        private TwigService $twig,
    ) {
        $this->formModel = $this->playableModelFactory->create(
            playableLinkModel: $this->playableLinkModelFactory->create(),
            contributionModel: $this->contributionModelFactory->create(),
        );
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $id = $routeParams[1] ?? null;
        return $this->formRequestHandler->respondToRequest($this->repo, $request, $this, $id);
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(?array $formData, ?string $id): Page
    {
        return $this->pageFactory->create(
            is_null($id) ? 'Nouveau jeu' : $formData['name'],
            self::class,
            is_null($id) ? [] : [$id],
            parentFqcn: AdminPlayableListController::class,
            isIndexed: false,
        );
    }

    public function getFormConfig(): array
    {
        return [
            'id' => [
                'required' => false,
            ],
            'links' => [
                'playable_id' => [
                    'ignore' => true,
                ]
            ],
            'contributions' => [
                'playable_id' => [
                    'ignore' => true,
                ]
            ],
        ];
    }

    public function getFormModel(): EntityModel
    {
        return $this->formModel;
    }

    public function prepareFormData(ServerRequestInterface $request, array $formData): array
    {
        $formData['id'] = $formData['id'] ?? $formData['name'] !== null ? (new Slug($formData['name'], true))->__toString() : null;
        foreach (array_keys($formData['contributions']) as $key) {
            $formData['contributions'][$key]['playable_id'] = $formData['id'];
        }
        foreach (array_keys($formData['links']) as $key) {
            $formData['links'][$key]['playable_id'] = $formData['id'];
        }

        return $formData;
    }

    public function respondToDeletion(string $entityId): ResponseInterface
    {
        $this->sessionManager->addMessage('Le jeu a bien été supprimé.');
        return $this->router->generateRedirect('admin-playable-list');
    }

    public function respondToInsertion(AppObject $entity): ResponseInterface
    {
        $this->repo->add($entity);
        $this->sessionManager->addMessage('Le jeu a bien été ajouté.');
        return $this->router->generateRedirect('admin-manage-playable', [$entity['id']]);
    }

    public function respondToUpdate(AppObject $entity, string $previousId): ResponseInterface
    {
        $this->repo->update($entity, $previousId);
        $this->sessionManager->addMessage('Le jeu a bien été mis à jour.');
        return new Response(body: 'HELLO');
        return $this->router->generateRedirect('admin-manage-playable', [$entity['id']]);
    }

    public function respondToNonPersistedRequest(
        ?array $formData,
        ?array $formErrors,
        ?array $deleteFormErrors,
        ?string $id,
    ): ResponseInterface {
        return $this->twig->respond(
            'admin_playable_form.html.twig',
            $this->getPage($formData, $id),
            [
                'authors' => $this->authorRepo->findAll(),
                'formData' => $formData,
                'formErrors' => $formErrors,
                'linkTypes' => LinkType::cases(),
                'playables' => $this->repo->findAll(),
                'playableTypes' => PlayableType::cases(),
                'requestedId' => $id,
            ],
        );
    }

    public function getUniqueConstraintFailureMessage(): string
    {
        return 'Un jeu avec cet identifiant existe déjà.';
    }
}