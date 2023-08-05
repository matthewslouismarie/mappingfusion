<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use MF\Session\SessionManager;
use MF\Model\Member;
use MF\Model\PasswordHash;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegistrationController implements ControllerInterface
{
    const ROUTE_ID = 'register';

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
        if ('POST' === $request->getMethod()) {
            $formData = $request->getParsedBody();
            $member = new Member($formData['username'], new PasswordHash(clear: $formData['password']));
            $this->repo->add($member);
        }
        return new Response(body: $this->twig->render('register.html.twig'));
    }

    public function getAccessControl(): Clearance {
        return Clearance::VISITORS;
    }
}