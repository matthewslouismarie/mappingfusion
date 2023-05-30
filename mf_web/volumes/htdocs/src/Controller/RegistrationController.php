<?php

namespace MF\Controller;

use MF\ModelFactory\FormMemberFactory;
use MF\Repository\MemberRepository;
use MF\Request;
use MF\TwigService;
use Twig\Environment;

class RegistrationController
{
    private Environment $twig;
    private MemberRepository $repo;

    public function __construct(
        TwigService $twigService,
        MemberRepository $repo,
    ) {
        $this->twig = $twigService->getTwig();
        $this->repo = $repo;
    }

    public function processRequest(Request $request): string {
        if ('POST' === $request->getMethod()) {
            $member = (new FormMemberFactory())->createFromRequest($request->getParsedBody());
            $this->repo->add($member);
        }
        return $this->twig->load('register.html.twig')->render();
    }
}