<?php

namespace MF\Controller;

use DomainException;
use GuzzleHttp\Psr7\Response;
use MF\Enum\LinkType;
use MF\Form;
use MF\Model\Entity;
use MF\Model\Playable;
use MF\Model\PlayableLink;
use MF\Repository\AuthorRepository;
use MF\Repository\PlayableRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PlayableController implements ControllerInterface
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

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $data = $this->getEntityDataFromRequest($request);

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
                'playables' => $this->repo->findAll(),
            ]),
        );
    }

    private function getEntityDataFromRequest(ServerRequestInterface $request): ?array {
        if ('POST' === $request->getMethod()) {
            return $request->getParsedBody();

            $data = $this->form->nullifyEmptyStrings($request->getParsedBody());

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
            return $entity->toArray();
        } else {
            return null;
        }
    }

    public function getAccessControl(): int {
        return 1;
    }
}