<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\PlayableRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminPlayableListController implements ControllerInterface, SinglePageOwner
{
    public function __construct(
        private PageFactory $pageFactory,
        private PlayableRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return $this->twig->respond(
            'admin_playable_list.html.twig',
            $this->getPage(),
            [
                'playables' => $this->repo->findAll(),
            ],
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page {
        return $this->pageFactory->create(
            name: 'Gestion des jeux',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}