<?php

namespace MF\Controller;

use MF\ModelFactory\FormMemberFactory;
use MF\Request;
use Twig\Environment;

class RegistrationController
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function processRequest(Request $request): string {
        if ('POST' === $request->getMethod()) {
            $member = (new FormMemberFactory())->createFromRequest($request->getParsedBody());
        }
        return $this->twig->load('register.html.twig')->render();
    }
}