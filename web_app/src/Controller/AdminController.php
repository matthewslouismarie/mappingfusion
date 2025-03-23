<?php

declare(strict_types=1);

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Form\FormFactory;
use LM\WebFramework\Model\Type\DataArrayModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Session\SessionManager;
use LM\WebFramework\Validation\Validator;
use MF\Database\DatabaseManager;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

final class AdminController implements IController
{
    public function __construct(
        private DatabaseManager $dbManager,
        private FormFactory $formFactory,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $message = null;
        $model = new DataArrayModel([]);
        $form = $this->formFactory->createForm($model);
        if ('POST' === $request->getMethod()) {
            // This automatically validates the CSRF token.
            $formData = $form->transformSubmittedData($request->getParsedBody(), $request->getUploadedFiles());
            $this->dbManager->createViews();
            $message = 'Les vues ont bien été régénérées.';
        }
    
        return $this->twig->respond(
            'admin/index.html.twig',
            $this->getPage(),
            [
                'message' => $message,
            ]
        );
    }

    public function getPage(): Page
    {
        return $this->pageFactory->createPage(
            'Centre d’Administration',
            self::class,
            [],
            HomeController::class,
            [],
            false,
            true,
        );
    }
}