<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use MF\HttpBridge\Session;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountController implements ControllerInterface
{
    const ROUTE_ID = 'manage_account';

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

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $member = $this->repo->find($this->session->getCurrentMemberUsername());
        $success = null;
        if ('POST' === $request->getMethod()) {
            $newPasswordHash = password_hash($request->getParsedBody()['password'], PASSWORD_DEFAULT);
            $this->repo->updateMember($member->setPasswordHash($newPasswordHash));
            $success = 'Votre mot de passe a été mis à jour.';
        }
        return new Response(body: $this->twig->render('account.html.twig', ['success' => $success]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}