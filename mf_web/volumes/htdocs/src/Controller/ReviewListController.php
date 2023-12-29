<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class ReviewListController implements ControllerInterface
{
    public function __construct(
        private ArticleRepository $repo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        return new Response(body: $this->twig->render('review_list.html.twig', [
            'reviews' => $this->repo->findAllReviews(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}