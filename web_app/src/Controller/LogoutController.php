<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\Session\SessionManager;
use MF\Repository\AccountRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutController implements IController, SinglePageOwner
{
    public function __construct(
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
        $formError = null;
        if ('POST' === $request->getMethod()) {
            $this->session->setCurrentUsername(null);
            return $this->twig->respond(
                'success.html.twig',
                $this->getPage(),
                [
                    'message' => 'Vous avez été déconnecté.',
                    'title' => 'Déconnecté',
                ],
            );
        }
        return $this->twig->respond(
            'admin/deconnexion.html.twig',
            $this->getPage(),
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name: 'Déconnexion',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
        );
    }
}