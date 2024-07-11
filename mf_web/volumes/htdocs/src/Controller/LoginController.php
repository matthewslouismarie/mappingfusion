<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Type\ModelValidator;
use MF\Model\MemberModel;
use MF\Repository\MemberRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController implements ControllerInterface
{
    public function __construct(
        private FormFactory $formFactory,
        private MemberRepository $repo,
        private ModelValidator $validator,
        private PageFactory $pageFactory,
        private SessionManager $session,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo Are there HTTP response codes or attributes for login required / successful?
     * @todo Redirect to success / error page instead?
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $model = (new MemberModel())->removeProperty('author_id');
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

    public function getAccessControl(): Clearance {
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