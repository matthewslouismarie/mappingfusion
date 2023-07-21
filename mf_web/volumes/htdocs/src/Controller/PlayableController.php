<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class PlayableController implements ControllerInterface
{
    const ROUTE_ID = 'playable';

    public function __construct(
        private PlayableRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request): Response {
        $id = $request->getQueryParams()['id'];
        $playable = $this->repo->find($id);
    
        return new Response(body: $this->twig->render('playable.html.twig', [
            'playable' => $playable,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}