<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Exception\Http\BadRequestException;
use MF\Exception\Http\NotFoundException;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

// Disabled.
class PlayableController implements ControllerInterface
{
    const ROUTE_ID = 'jeu';

    public function __construct(
        private PlayableRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        if (!key_exists(1, $routeParams)) {
            throw new BadRequestException();
        }
        $id = $routeParams[1];
        $playable = $this->repo->find($id);
        if (null === $playable) {
            throw new NotFoundException();
        }
    
        return new Response(body: $this->twig->render('playable.html.twig', [
            'playable' => $playable,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}