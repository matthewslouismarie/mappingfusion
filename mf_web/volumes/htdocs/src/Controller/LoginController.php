<?php

namespace MF\Controller;

use MF\HttpBridge\Session;
use MF\ModelFactory\FormMemberFactory;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController implements ControllerInterface
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
            $member = $this->repo->find($request->getParsedBody()['username']);
            if (null === $member || !password_verify($request->getParsedBody()['password'], $member->getPasswordHash())) {
                $formError = 'Identifiants invalides.';
            } else {
                $this->session->setCurrentMemberUsername($member->getUsername());
            }
        }
        return new Response(body: $this->twig->render('login.html.twig', [
            'formError' => $formError,
        ]));
    }

    // @todo Use enum? Or class?
    public function getAccessControl(): int {
        return -1;
    }
}