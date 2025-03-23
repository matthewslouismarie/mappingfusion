<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\StringModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Model\AccountModelFactory;
use MF\Repository\AuthorRepository;
use MF\Repository\AccountRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountController implements IController, SinglePageOwner
{
    public function __construct(
        private AuthorRepository $authorRepository,
        private FormFactory $formFactory,
        private AccountModelFactory $accountModelFactory,
        private AccountRepository $repo,
        private PageFactory $pageFactory,
        private SessionManager $session,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $account = $this->repo->find($this->session->getCurrentUsername());

        $model = $this->accountModelFactory
            ->create()
            ->prune(['id', 'author_id'])
            ->addProperty('password', new StringModel(isNullable: true))
        ;
        
        $form = $this->formFactory->createForm($model);
        $formData = $account;
        $formErrors = null;

        if ('POST' === $request->getMethod()) {
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $validator = new Validator($model);
            $formErrors = $validator->validate($formData, $model);
            if (0 === count($formErrors)) {
                $appObject = new AppObject($formData);
                if (null !== $formData['password']) {
                    $this->repo->update($appObject, $this->session->getCurrentUsername());
                } else {
                    $this->repo->updateExceptPassword($appObject, $this->session->getCurrentUsername());
                }
                $this->session->setCurrentUsername($appObject['id']);
                $this->session->addMessage('Votre compte a été mis à jour.');
            }
        }

        return $this->twig->respond(
            'account.html.twig',
            $this->getPage(),
            [
                'authors' => $this->authorRepository->findAll(),
                'formData' => $formData,
                'formErrors' => $formErrors,
                'account' => $account,
            ],
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name: 'Gestion du compte',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}