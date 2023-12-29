<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

// Disabled.
class PlayableController implements ControllerInterface
{
    public function __construct(
        private PlayableRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        if (!key_exists(1, $routeParams)) {
            throw new RequestedResourceNotFound();
        }
        $id = $routeParams[1];
        $playable = $this->repo->find($id);
        if (null === $playable) {
            throw new RequestedResourceNotFound();
        }
    
        return new Response(body: $this->twig->render('playable.html.twig', [
            'playable' => $playable,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}