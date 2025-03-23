<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Model\AccountModelFactory;
use MF\Repository\AccountRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController implements IController
{
    public function __construct(
        private FormFactory $formFactory,
        private AccountModelFactory $accountModelFactory,
        private AccountRepository $repo,
        private PageFactory $pageFactory,
        private SessionManager $session,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo Are there HTTP response codes or attributes for login required / successful?
     * @todo Redirect to success / error page instead?
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $model = $this->accountModelFactory->create()->prune(['id', 'password']);
        $form = $this->formFactory->createTransformer($model);
        $formErrors = [];
        $formData = null;

        if ('POST' === $request->getMethod()) {
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $validator = new Validator($model);
            $formErrors = $validator->validate($formData, $model);
            if (0 === count($formErrors)) {
                $account = $this->repo->find($formData['id']);
                if (null === $account || !password_verify($request->getParsedBody()['password'], $account['password'])) {
                    $formErrors[] = 'Identifiants invalides.';
                } else {
                    $this->session->setCurrentUsername($account['id']);
                    return $this->twig->respond(
                        'success.html.twig',
                        $this->getPage(),
                        [
                            'message' => 'Connexion réussie.',
                        ],
                    );
                }
            }
        }
        return $this->twig->respond(
            'login.html.twig',
            $this->getPage(),
            [
                'formErrors' => $formErrors,
                'formData' => $formData,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::VISITORS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->createPage(
            name: 'Connexion',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
        );
    }
}