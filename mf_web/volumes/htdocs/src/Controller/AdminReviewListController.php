<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\ReviewRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminReviewListController implements ControllerInterface, SinglePageOwner
{
    public function __construct(
        private PageFactory $pageFactory,
        private ReviewRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {   
        return $this->twig->respond(
            'admin_review_list.html.twig',
            $this->getPage(),
            [
            'reviews' => $this->repo->findAll(),
            ],
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name: 'Gestion des tests',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}