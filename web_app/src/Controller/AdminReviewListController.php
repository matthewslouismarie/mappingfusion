<?php

namespace MF\Controller;

use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Model\ReviewModelFactory;
use MF\Repository\ReviewRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminReviewListController implements IController, SinglePageOwner
{
    public function __construct(
        private PageFactory $pageFactory,
        private ReviewRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        return $this->twig->respond(
            'admin_review_list.html.twig',
            $this->getPage(),
            [
                'reviews' => $this->repo->findAll(),
                'MAX_RATING' => ReviewModelFactory::RATING_MAX,
            ],
        );
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