<?php

namespace MF\Controller;

use MF\Framework\DataStructures\AppObject;
use MF\Enum\Clearance;
use MF\Framework\Form\FormFactory;
use MF\Framework\Model\StringModel;
use MF\Framework\Type\ModelValidator;
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
        private FormFactory $formFactory,
        private MemberRepository $repo,
        private ModelValidator $modelValidator,
        private SessionManager $session,
        private TwigService $twig,
    ) {
    }    

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $model = (new MemberModel())
            ->removeProperty('password')
            ->addProperty('password', new StringModel(isNullable: true))
        ;

        $formData = [
            'id' => $this->session->getCurrentMemberUsername(),
            'password' => null,
        ];
        $formErrors = null;
        $success = null;
        $form = $this->formFactory->createForm($model);
        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formErrors = $this->modelValidator->validate($formData, $model);
            if (0 === count($formErrors)) {
                if (null !== $formData['password']) {
                    $success = 'Votre compte a été mis à jour.';
                    $formData['password'] =  password_hash($formData['password'], PASSWORD_DEFAULT);
                    $this->repo->updateMember(new AppObject($formData), $this->session->getCurrentMemberUsername());
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