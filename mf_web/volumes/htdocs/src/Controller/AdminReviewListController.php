<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ReviewRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminReviewListController implements ControllerInterface
{
    const ROUTE_ID = 'admin-review-list';

    public function __construct(
        private ReviewRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {   
        return new Response(body: $this->twig->render('admin_review_list.html.twig', [
            'reviews' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}