<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Auth\WebAuthn;
use MF\Model\AccountModelFactory;
use MF\Model\PublicKeyCredentialModelFactory;
use MF\Repository\AuthorRepository;
use MF\Repository\AccountRepository;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountAddPasskey implements IController
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private FormFactory $formFactory,
        private AccountModelFactory $accountModelFactory,
        private AccountRepository $repo,
        private PageFactory $pageFactory,
        private SessionManager $session,
        private Router $router,
        private TwigService $twig,
        private WebAuthn $webAutn,
        private PublicKeyCredentialModelFactory $publicKeyCredentialModelFactory,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $model = $this->publicKeyCredentialModelFactory->create();
        $form = $this->formFactory->createForm($model);

        if ('POST' === $request->getMethod()) {
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $validator = new Validator($model); // @todo check JSON format with JSON schema
            $formErrors = $validator->validate($formData, $model);
            if (0 === count($formErrors)) {
                $credential = $formData['public-key-credential'];
                $this->webAutn->registerCredential($credential);
                $this->session->addMessage('La passkey a bien été ajoutée.');
                return $this->router->redirect(AccountController::class);
            }
        }
        
        return $this->twig->respond(
            'add_passkey_form.html.twig',
            $this->getPage(),
            [
                'publicKeyCredentialCreationOptions' => json_encode($this->webAutn->getPublicKeyCredentialCreationOptions()),
                'challenge' => $this->session->getCustom(WebAuthn::SESSION_KEY),
            ],
        );
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name: 'Ajouter une passkey au compte',
            controllerFqcn: self::class,
            parentFqcn: AccountController::class,
            isIndexed: false,
        );
    }
}