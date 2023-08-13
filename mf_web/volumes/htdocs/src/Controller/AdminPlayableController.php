<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\DataStructure\AppObject;
use MF\Enum\Clearance;
use MF\Enum\LinkType;
use MF\Exception\Database\EntityNotFoundException;
use MF\Exception\Http\NotFoundException;
use MF\Model\Playable;
use MF\Repository\AuthorRepository;
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
        private TwigService $twig,
        private Router $router,
        private PlayableRepository $repo,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $playableId = $request->getQueryParams()['id'] ?? null;
        $playable = $this->getPlayable($routeParams);

        // update entity if post request

        // @todo throw exception in getEntityDataFromRequest instead
        // if (null === $entityData && isset($request->getQueryParams()['id'])) {
        //     return new Response(302, ['Location' => $this->router->generateUrl(self::ROUTE_ID, ['id' => strval($entity->getId())])]);
        // }

        return new Response(
            body: $this->twig->render('playable_form.html.twig', [
                'authors' => $this->authorRepo->findAll(),
                'data' => $playable,
                'linkTypes' => LinkType::cases(),
                'playableId' => $playableId,
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