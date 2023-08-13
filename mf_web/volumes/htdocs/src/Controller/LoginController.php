<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use MF\Form\FormFactory;
use MF\Model\MemberModel;
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
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $form = $this->formFactory->createForm(new MemberModel());
        $formErrors = [];
        $formData = null;

        if ('POST' === $request->getMethod()) {
            $submission = $form->extractFormData($request->getParsedBody());
            if (!$submission->hasErrors()) {
                $formData = $submission->getData();
                $member = $this->repo->find($formData['id']);
                if (null === $member || !password_verify($request->getParsedBody()['password'], $member->password)) {
                    $formErrors[] = 'Identifiants invalides.';
                } else {
                    $this->session->setCurrentMemberUsername($member->id);
                    return new Response(body: $this->twig->render('success.html.twig', [
                        'message' => 'Connexion réussie.',
                        'title' => 'Connecté',
                    ]));
                }
            }
        }
        return new Response(body: $this->twig->render('login.html.twig', [
            'formErrors' => $formErrors,
            'formData' => $formData,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::VISITORS;
    }
}