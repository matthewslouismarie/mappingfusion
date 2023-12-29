<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorPageNotFoundController implements ControllerInterface
{
    public function __construct(
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(
            status: 404,
            body: $this->twig->render('errors/error_page.html.twig', [
                'message' => 'Cette page n’existe pas… :(',
                'title' => 'Page non trouvé',
            ]),
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ALL;
    }
}