<?php

namespace MF\Controller;

use MF\HttpBridge\Session;
use MF\ModelFactory\FormMemberFactory;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegistrationController implements ControllerInterface
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
        if ('POST' === $request->getMethod()) {
            $member = (new FormMemberFactory())->createFromRequest($request->getParsedBody());
            $this->repo->add($member);
        }
        return new Response(body: $this->twig->render('register.html.twig'));
    }

    // @todo Use enum? Or class?
    public function getAccessControl(): int {
        return -1;
    }
}