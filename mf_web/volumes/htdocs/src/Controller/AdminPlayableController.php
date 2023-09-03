<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Framework\DataStructures\AppObject;
use MF\Enum\Clearance;
use MF\Enum\LinkType;
use MF\Exception\Database\EntityNotFoundException;
use MF\Exception\Http\NotFoundException;
use MF\Framework\Form\FormFactory;
use MF\Framework\Type\ModelValidator;
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
    const ROUTE_ID = 'manage_playable';

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

        $playableId = $routeParams[1] ?? null;

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
                if (null === $playableId) {
                    $this->repo->add($playable);
                } else {
                    $this->repo->update($playable, $playableId);
                }
                return $this->router->generateRedirect(self::ROUTE_ID, [$playable->id]);
            }
        } elseif (isset($routeParams[1])) {
            try {
                $formData = $this->repo->findOne($routeParams[1])->toArray();
            } catch (EntityNotFoundException $e) {
                throw new NotFoundException();
            }
        }

        return new Response(
            body: $this->twig->render('playable_form.html.twig', [
                'authors' => $this->authorRepo->findAll(),
                'formData' => $formData,
                'formErrors' => $formErrors,
                'linkTypes' => LinkType::cases(),
                'playables' => $this->repo->findAll(),
            ]),
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}