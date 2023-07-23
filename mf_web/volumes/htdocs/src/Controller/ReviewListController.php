<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ReviewRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class ReviewListController implements ControllerInterface
{
    const ROUTE_ID = 'reviews';

    public function __construct(
        private ReviewRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {

        return new Response(body: $this->twig->render('review_list.html.twig', [
            'reviews' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}