<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\DataStructure\AppObject;
use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Enum\LinkType;
use MF\Exception\Database\EntityNotFoundException;
use MF\Exception\Http\NotFoundException;
use MF\Form\FormFactory;
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
        private AppObjectFactory $appObjectFactory,
        private FormFactory $formFactory,
        private PlayableRepository $repo,
        private PlayableLinkRepository $linkRepo,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $model = new PlayableModel(
            playableLinkModel: new PlayableLinkModel(),
            contributionModel: new ContributionModel(),
        );
        $existingPlayable = $this->getPlayable($routeParams);

        $form = $this->formFactory->createForm($model, formConfig: [
            'id' => [
                'required' => false,
            ],
            'links' => [
                'playable_id' => [
                    'generated' => true,
                ]
            ],
            'contributions' => [
                'playable_id' => [
                    'generated' => true,
                ]
            ],
        ]);
    
        $formData = $form->createFromAppArray($existingPlayable->toArray())->getChildren();

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFromRequest($request->getParsedBody());
            $formData = $submission->getChildren();

            // if (!$submission->hasErrors()) {
            //     $submittedData['id'] = $submittedData['id'] ?? (new Slug($submittedData['name'], true))->__toString();
            //     $playable = $this->appObjectFactory->create($submittedData, new PlayableModel());
            //     if (null === $existingPlayable) {
            //         $this->repo->add($playable);
            //     } else {
            //         $this->repo->update($playable, $existingPlayable->id);
            //     }
            //     foreach ($submittedData['links'] as $link) {
            //         $link['playable_id'] = $playable->id;
            //         if (isset($link['id'])) {
            //             $this->linkRepo->update($this->appObjectFactory->create($link, new PlayableLinkModel()));
            //         } else {
            //             $this->linkRepo->add($this->appObjectFactory->create($link, new PlayableLinkModel()));
            //         }
            //     }
            //     if (null === $existingPlayable || $playable->id !== $existingPlayable->id) {
            //         return $this->router->generateRedirect(self::ROUTE_ID, [$playable->id]);
            //     }
            // }
        }

        return new Response(
            body: $this->twig->render('playable_form.html.twig', [
                'authors' => $this->authorRepo->findAll(),
                'formData' => $formData,
                'linkTypes' => LinkType::cases(),
                'playables' => $this->repo->findAll(),
            ]),
        );
    }

    private function getPlayable(array $routeParams): ?AppObject {
        try {
            return key_exists(1, $routeParams) ? $this->repo->findOne($routeParams[1]) : null;
        } catch (EntityNotFoundException $e) {
            throw new NotFoundException();
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}