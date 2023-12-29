<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorAccessDeniedController implements ControllerInterface
{
    public function __construct(
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(
            status: 404,
            body: $this->twig->render('errors/error_page.html.twig', [
                'message' => 'Désolé monsieur Freeman, l’accès est interdit… ou alors il faut vous connecter.',
                'title' => 'Accès interdit',
            ]),
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ALL;
    }
}