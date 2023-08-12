<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use MF\Form\FormFactory;
use MF\Session\SessionManager;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController implements ControllerInterface
{
    const ROUTE_ID = 'login';

    public function __construct(
        private FormFactory $formFactory,
        private MemberRepository $repo,
        private SessionManager $session,
        private TwigService $twigService,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $formError = null;

        $form = $this->formFactory->createForm()
        if ('POST' === $request->getMethod()) {
            $member = $this->repo->find($request->getParsedBody()['username']);
            if (null === $member || !password_verify($request->getParsedBody()['password'], $member->password)) {
                $formError = 'Identifiants invalides.';
            } else {
                $this->session->setCurrentMemberUsername($member->id);
                return new Response(body: $this->twig->render('success.html.twig', [
                    'message' => 'Connexion réussie.',
                    'title' => 'Connecté',
                ]));
            }
        }
        return new Response(body: $this->twig->render('login.html.twig', [
            'formError' => $formError,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::VISITORS;
    }
}