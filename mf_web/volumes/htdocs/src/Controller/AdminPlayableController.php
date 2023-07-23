<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Enum\LinkType;
use MF\Form;
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
        private Form $form,
        private TwigService $twig,
        private Router $router,
        private PlayableRepository $repo,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $playableId = $request->getQueryParams()['id'] ?? null;
        $data = $this->getEntityDataFromRequest($request, $playableId);

        // update entity if post request

        // @todo throw exception in getEntityDataFromRequest instead
        // if (null === $entityData && isset($request->getQueryParams()['id'])) {
        //     return new Response(302, ['Location' => $this->router->generateUrl(self::ROUTE_ID, ['id' => strval($entity->getId())])]);
        // }

        return new Response(
            body: $this->twig->render('playable_form.html.twig', [
                'authors' => $this->authorRepo->findAll(),
                'data' => $data,
                'linkTypes' => LinkType::cases(),
                'playableId' => $playableId,
                'playables' => $this->repo->findAll(),
            ]),
        );
    }

    private function getEntityDataFromRequest(ServerRequestInterface $request, ?string $playableId): ?array {
        if ('POST' === $request->getMethod()) {
            $data = $request->getParsedBody();

            if (key_exists('playable_stored_links', $data)) {
                for ($i = 0; $i < count($data['playable_stored_links']); $i++) {
                    $data['playable_stored_links'][$i]['playable_id'] = $request->getQueryParams()['id'] ?? null;
                }
            }

            $entity = Playable::fromArray($data, linkPrefix: '');
            if (null !== $playableId) {
                $this->repo->update($playableId, $entity, false, $data['links-to-remove'] ?? []);
            } else {
                $this->repo->add($entity);
            }
            
            return $data;
        } elseif (null !== $playableId) {
            $entity = $this->repo->find($playableId);
            if (null === $entity) {
                throw new DomainException();
            }
            return $entity->toArray(linkPrefix: '');
        } else {
            return null;
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}