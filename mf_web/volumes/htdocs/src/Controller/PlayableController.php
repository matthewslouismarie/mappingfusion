<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\Model\Entity;
use MF\Model\Playable;
use MF\Repository\PlayableRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayableController implements ControllerInterface
{
    const ROUTE_ID = 'manage_playable';

    public function __construct(
        private TwigService $twig,
        private Router $router,
        private PlayableRepository $repo,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $entity = $this->getFromRequest($request);

        if (null !== $entity && (!isset($request->getQueryParams()['id']) || $entity->getId() !== $request->getQueryParams()['id'])) {
            return new Response(302, ['Location' => $this->router->generateUrl(self::ROUTE_ID, ['id' => strval($entity->getId())])]);
        }

        return new Response(
            body: $this->twig->render('playable_form.html.twig', [
                'entity' => $entity?->toArray(),
                'playables' => $this->repo->findAll(),
            ])
        );
    }

    private function getFromRequest(ServerRequestInterface $request): ?Entity {
        if ('POST' === $request->getMethod()) {
            $data = $request->getParsedBody();
            
            if ('' === $data['p_id']) {
                $data['p_id'] = null;
            }
            
            if ('' === $data['p_game_id']) {
                $data['p_game_id'] = null;
            }

            if (isset($request->getQueryParams()['id'])) {
                $entity = Playable::fromArray($data);
                $this->repo->update($request->getQueryParams()['id'], $entity);

                return $entity;
            } else {
                $entity = Playable::fromArray($data);
                $this->repo->add($entity);
                return $entity;
            }
        } elseif (isset($request->getQueryParams()['id'])) {
            $entity = $this->repo->find($request->getQueryParams()['id']);
            if (null === $entity) {
                throw new DomainException();
            }
            return $entity;
        } else {
            return null;
        }
    }

    public function getAccessControl(): int {
        return 1;
    }
}