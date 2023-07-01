<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Model\Review;
use MF\Repository\PlayableRepository;
use MF\Repository\ReviewRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

class ReviewController implements ControllerInterface
{
    const ROUTE_ID = 'manage_review';

    private PlayableRepository $playableRepo;

    private ReviewRepository $repo;

    private Router $router;

    private TwigService $twig;

    public function __construct(
        PlayableRepository $playableRepo,
        ReviewRepository $repo,
        Router $router,
        TwigService $twig,
    ) {
        $this->playableRepo = $playableRepo;
        $this->repo = $repo;
        $this->router = $router;
        $this->twig = $twig;
    }    

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $id = $request->getQueryParams()['id'] ?? null;
        $entity = $this->retrieveEntityFromRequest($request, $id);

        if ('POST' === $request->getMethod()) {
            if (null === $id) {
                $updatedEntity = $this->repo->add($entity);
                return $this->router->generateRedirect(self::ROUTE_ID, ['id' => $updatedEntity->getId()]);
            } else {
                $this->repo->update($entity);
            }
        }

        return new Response(body: $this->twig->render('review_form.html.twig', [
            'entity' => $entity?->toArray(),
            'playables' => $this->playableRepo->findAll(),
        ]));
    }

    public function getAccessControl(): int {
        return 1;
    }

    private function retrieveEntityFromRequest(ServerRequestInterface $request, ?string $id): ?Review {
        if ('POST' === $request->getMethod()) {
            return Review::fromArray(['review_id' => $id ?? null] + $request->getParsedBody());
        } elseif (null !== $id) {
            $entity = $this->repo->find($id);
            if (null === $entity) {
                throw new UnexpectedValueException();
            }
            return $entity;
        } else {
            return null;
        }
    }
}