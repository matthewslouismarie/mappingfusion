<?php

namespace MF\Controller;

use MF\HttpBridge\Session;
use MF\ModelFactory\FormMemberFactory;
use MF\Repository\MemberRepository;
use MF\Request;
use MF\TwigService;

class AuthController
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

    public function handleRegistrationPage(Request $request): string {
        if ('POST' === $request->getMethod()) {
            $member = (new FormMemberFactory())->createFromRequest($request->getParsedBody());
            $this->repo->add($member);
        }
        return $this->twig->render('register.html.twig');
    }

    public function handleLoginPage(Request $request): string {
        $formError = null;
        if ('POST' === $request->getMethod()) {
            $member = $this->repo->find($request->getParsedBody()['username']);
            if (null === $member || !password_verify($request->getParsedBody()['password'], $member->getPasswordHash())) {
                $formError = 'Identifiants invalides.';
            } else {
                $this->session->setCurrentMemberUsername($member->getUsername());
            }
        }
        return $this->twig->render('login.html.twig', [
            'formError' => $formError,
        ]);
    }
}