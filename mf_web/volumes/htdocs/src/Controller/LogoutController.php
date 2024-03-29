<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\MemberRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutController implements ControllerInterface
{
    private TwigService $twig;
    private MemberRepository $repo;

    private SessionManager $session;

    public function __construct(
        MemberRepository $repo,
        SessionManager $session,
        TwigService $twigService,
    ) {
        $this->twig = $twigService;
        $this->repo = $repo;
        $this->session = $session;
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $formError = null;
        if ('POST' === $request->getMethod()) {
            $this->session->setCurrentMemberUsername(null);
            return new Response(
                body: $this->twig->render('success.html.twig', [
                    'message' => 'Vous avez étés déconnectés.',
                    'title' => 'Déconnecté',
                ]),
            );
        }
        return new Response(body: $this->twig->render('logout.html.twig'));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}