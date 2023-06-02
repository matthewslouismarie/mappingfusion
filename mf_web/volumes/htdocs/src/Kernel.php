<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use MF\Controller\AccountController;
use MF\Controller\ArticleController;
use MF\Controller\AuthController;
use MF\Controller\ControllerInterface;
use MF\Controller\HomeController;
use MF\HttpBridge\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

// @todo Move http bridge code away from the rest of the code, and call it in index.php?
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

    public function generateResponse(ControllerInterface $controller, ServerRequestInterface $requestInterface): Response {
        if (-1 === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous ne pouvez pas accéder à cette page.',
                    'title' => 'Accès non autorisé',
                ]),
            );
        } elseif (1 === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous devez vous connecter pour accéder à cette page.',
                    'title' => 'Connexion requise',
                ]),
            );
        }
        return $controller->generateResponse($requestInterface);
    }
}