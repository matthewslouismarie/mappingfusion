<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController implements ControllerInterface
{
    const ROUTE_ID = 'home';

    public function __construct(
        private ArticleRepository $articleRepo,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(body: $this->twig->render('home.html.twig', [
            'featured_articles' => $this->articleRepo->findFeatured(),
            'reviews' => $this->articleRepo->findAllReviews(),
            'last_articles' => $this->articleRepo->findLastArticles(),
            'last_reviews' => $this->articleRepo->findLastReviews(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}