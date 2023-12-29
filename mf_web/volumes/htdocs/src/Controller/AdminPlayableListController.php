<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPlayableListController implements ControllerInterface
{
    public function __construct(
        private PlayableRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_playable_list.html.twig', [
            'playables' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}