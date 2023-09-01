<?php

namespace MF\Controller;

use MF\DataStructure\AppObjectFactory;
use MF\Enum\Clearance;
use MF\Framework\Form\FormFactory;
use MF\Model\MemberModel;
use MF\Session\SessionManager;
use MF\Repository\MemberRepository;
use GuzzleHttp\Psr7\Response;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountController implements ControllerInterface
{
    const ROUTE_ID = 'manage-account';

    public function __construct(
        private MemberRepository $repo,
        private SessionManager $session,
        private TwigService $twig,
        private FormFactory $formFactory,
        private AppObjectFactory $appObjectFactory,
        private MemberModel $model,
    ) {
    }    

    /**
     * @todo Use standard form.
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $formData = [
            'id' => $this->session->getCurrentMemberUsername(),
            'password' => null,
        ];
        $formErrors = null;
        $success = null;
        $form = $this->formFactory->createForm($this->model, formConfig: [
            'password' => [
                'required' => false,
            ],
        ]);
        if ('POST' === $request->getMethod()) {
            $submitted = $form->extractFromRequest($request->getParsedBody());
            $formData = $submitted->getContent();
            $formErrors = $submitted->getErrors();
            if (!$submitted->hasErrors()) {
                if (null !== $formData['password']) {
                    $success = 'Votre compte a été mis à jour.';
                    $formData['password'] =  password_hash($formData['password'], PASSWORD_DEFAULT);
                    $this->repo->updateMember($this->appObjectFactory->create($formData, $this->model));
                    $this->session->setCurrentMemberUsername($formData['id']);
                } else {
                    $this->repo->updateId($this->session->getCurrentMemberUsername(), $formData['id']);
                    $success = 'Votre nom d’utilisateur a été mis à jour.';
                    $this->session->setCurrentMemberUsername($formData['id']);
                }
            }
        }
        return new Response(body: $this->twig->render('account.html.twig', [
            'success' => $success,
            'formData' => $formData,
            'formErrors' => $formErrors,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}