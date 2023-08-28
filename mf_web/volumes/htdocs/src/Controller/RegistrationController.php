<?php

namespace MF\Controller;

use Exception;
use MF\Enum\Clearance;
use MF\Session\SessionManager;
use MF\Repository\MemberRepository;
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
        throw new Exception();
    }

    public function getAccessControl(): Clearance {
        return Clearance::VISITORS;
    }
}