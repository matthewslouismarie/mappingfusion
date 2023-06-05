<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPlayableListController implements ControllerInterface
{
    const ROUTE_ID = 'admin_playable_list';

    private PlayableRepository $repo;

    private TwigService $twig;

    public function __construct(
        PlayableRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_playable_list.html.twig', [
            'playables' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): int {
        return 1;
    }
}