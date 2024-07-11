<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminCategoryListController implements ControllerInterface, SinglePageOwner
{
    public function __construct(
        private CategoryRepository $repo,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return $this->twig->respond(
            'admin_category_list.html.twig',
            $this->getPage(),
            [
                'categories' => $this->repo->findAll(),
            ],
        );
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }

    public function getPage(): Page
    {
        return $this->pageFactory->create(
            name: 'Gestion des catégories',
            controllerFqcn: self::class,
            parentFqcn: HomeController::class,
            isIndexed: false,
        );
    }
}