<?php

namespace MF\Controller;

use MF\HttpBridge\Session;
use MF\ModelFactory\FormMemberFactory;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutController implements ControllerInterface
{
    private TwigService $twig;
    private MemberRepository $repo;

    private Session $session;

    public function __construct(
        MemberRepository $repo,
        Session $session,
        TwigService $twigService,
    ) {
        $this->twig = $twigService;
        $this->repo = $repo;
        $this->session = $session;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
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

    public function getAccessControl(): int {
        return 1;
    }
}