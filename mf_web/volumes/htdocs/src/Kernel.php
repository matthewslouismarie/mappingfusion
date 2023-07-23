<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use MF\Controller\ControllerInterface;
use MF\Enum\Clearance;
use MF\HttpBridge\Session;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{
    private TwigService $twig;

    private Session $session;

    public function __construct(
        Session $session,
        TwigService $twig,
    ) {
        $this->session = $session;
        $this->twig = $twig;
    }

    public function extractRouteParams(ServerRequestInterface $request): array {
        return explode('/', $request->getQueryParams()['route_params']);
    }

    public function generateResponse(ControllerInterface $controller, ServerRequestInterface $request): Response {
        if (Clearance::VISITORS === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous ne pouvez pas accéder à cette page.',
                    'title' => 'Accès non autorisé',
                ]),
            );
        } elseif (Clearance::ADMINS === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous devez vous connecter pour accéder à cette page.',
                    'title' => 'Connexion requise',
                ]),
            );
        }
        return $controller->generateResponse($request, $this->extractRouteParams($request));
    }
}