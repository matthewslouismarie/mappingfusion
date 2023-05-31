<?php

namespace MF\Controller;

use MF\HttpBridge\Session;
use MF\Repository\MemberRepository;
use MF\Request;
use MF\TwigService;

class AccountController
{
    private MemberRepository $repo;
    private Session $session;

    private TwigService $twig;

    public function __construct(
        MemberRepository $repo,
        Session $session,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->session = $session;
        $this->twig = $twig;
    }    

    public function handleAccountPage(Request $request): string {
        $member = $this->repo->find($this->session->getCurrentMemberUsername());
        $success = null;
        if ('POST' === $request->getMethod()) {
            $newPasswordHash = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
            $this->repo->updateMember($member->setPasswordHash($newPasswordHash));
            $success = 'Votre mot de passe a été mis à jour.';
        }
        return $this->twig->render('account.html.twig');
    }
}