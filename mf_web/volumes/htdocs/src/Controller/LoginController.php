<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use MF\Framework\Form\FormFactory;
use MF\Framework\Type\ModelValidator;
use MF\Model\MemberModel;
use MF\Session\SessionManager;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController implements ControllerInterface
{
    const ROUTE_ID = 'accès-g-man';

    public function __construct(
        private FormFactory $formFactory,
        private MemberRepository $repo,
        private ModelValidator $validator,
        private SessionManager $session,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $model = new MemberModel();
        $form = $this->formFactory->createTransformer($model);
        $formErrors = [];
        $formData = null;

        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formErrors = $this->validator->validate($formData, $model);
            if (0 === count($formErrors)) {
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