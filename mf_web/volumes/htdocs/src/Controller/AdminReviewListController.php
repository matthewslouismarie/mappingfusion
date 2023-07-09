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
    const ROUTE_ID = 'admin_review_list';

    private ReviewRepository $repo;

    private TwigService $twig;

    public function __construct(
        ReviewRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_review_list.html.twig', [
            'reviews' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}