<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\StringModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\MemberModel;
use MF\Repository\AuthorRepository;
use MF\Repository\MemberRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountController implements ControllerInterface
{
    public function __construct(
        private AuthorRepository $authorRepository,
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
        $form = $this->formFactory->createForm($model);
        if ('POST' === $request->getMethod()) {
            $formData = $form->extractValueFromRequest($request->getParsedBody(), $request->getUploadedFiles());
            $formErrors = $this->modelValidator->validate($formData, $model);
            if (0 === count($formErrors)) {
                if (null !== $formData['password']) {
                    $formData['password'] =  password_hash($formData['password'], PASSWORD_DEFAULT);
                    $this->repo->update($formData, $this->session->getCurrentMemberUsername());
                } else {
                    $this->repo->update($formData, $this->session->getCurrentMemberUsername(), false);
                }
                $this->session->setCurrentMemberUsername($formData['id']);
                $this->session->addMessage('Votre compte a été mis à jour.');
            }
        } else {
            $formData = $this->repo->find($this->session->getCurrentMemberUsername());
        }

        return new Response(body: $this->twig->render('account.html.twig', [
            'formData' => $formData,
            'formErrors' => $formErrors,
            'authors' => $this->authorRepository->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}