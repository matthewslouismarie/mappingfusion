<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Type\ModelValidator;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use MF\Exception\Database\EntityNotFoundException;
use MF\Model\ContributionModel;
use MF\Model\PlayableLinkModel;
use MF\Model\PlayableModel;
use MF\Model\Slug;
use MF\Repository\AuthorRepository;
use MF\Repository\PlayableLinkRepository;
use MF\Repository\PlayableRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPlayableController implements ControllerInterface
{
    public function __construct(
        private AuthorRepository $authorRepo,
        private FormFactory $formFactory,
        private ModelValidator $validator,
        private PlayableLinkRepository $linkRepo,
        private PlayableRepository $repo,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $model = new PlayableModel(
            playableLinkModel: new PlayableLinkModel(),
            contributionModel: new ContributionModel(),
        );

        $requestedId = $routeParams[1] ?? null;

        $form = $this->formFactory->createForm($model, config: [
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
        ]);
    
        $formErrors = null;
        $formData = null;

        if ('POST' === $request->getMethod()) {
            // Form Data extraction, generation and validation.
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formData['id'] = $formData['id'] ?? $formData['name'] !== null ? (new Slug($formData['name'], true))->__toString() : null;
            foreach ($formData['contributions'] as $key => $c) {
                $formData['contributions'][$key]['playable_id'] = $formData['id'];
            }
            foreach ($formData['links'] as $key => $c) {
                $formData['links'][$key]['playable_id'] = $formData['id'];
            }
            $formErrors = $this->validator->validate($formData, $model);

            if (0 === count($formErrors)) {
                $playable = new AppObject($formData);
                if (null === $requestedId) {
                    $this->repo->addOrUpdate($playable, add: true);
                } else {
                    $this->repo->addOrUpdate($playable, $requestedId);
                }
                return $this->router->generateRedirect('admin-manage-playable', [$playable->id]);
            }
        } elseif (isset($routeParams[1])) {
            try {
                $formData = $this->repo->findOne($routeParams[1])->toArray();
            } catch (EntityNotFoundException $e) {
                throw new RequestedResourceNotFound();
            }
        }

        return new Response(
            body: $this->twig->render('admin_playable_form.html.twig', [
                'authors' => $this->authorRepo->findAll(),
                'formData' => $formData,
                'formErrors' => $formErrors,
                'linkTypes' => LinkType::cases(),
                'playables' => $this->repo->findAll(),
                'playableTypes' => PlayableType::cases(),
                'requestedId' => $requestedId,
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}