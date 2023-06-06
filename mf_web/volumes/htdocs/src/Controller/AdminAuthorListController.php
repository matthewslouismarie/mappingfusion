<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminAuthorListController implements ControllerInterface
{
    const ROUTE_ID = 'admin_author_list';

    private AuthorRepository $repo;

    private TwigService $twig;

    public function __construct(
        AuthorRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_author_list.html.twig', [
            'authors' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): int {
        return 1;
    }
}