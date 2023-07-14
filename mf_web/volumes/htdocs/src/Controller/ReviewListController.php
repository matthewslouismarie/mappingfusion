<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class ReviewListController implements ControllerInterface
{
    const ROUTE_ID = 'reviews';

    public function __construct(
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request): Response {
        return new Response(body: $this->twig->render('review_list.html.twig'));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}