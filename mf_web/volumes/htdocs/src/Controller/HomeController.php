<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController implements ControllerInterface
{
    private ArticleRepository $articleRepo;

    private TwigService $twig;

    public function __construct(
        ArticleRepository $articleRepo,
        TwigService $twig,
    ) {
        $this->articleRepo = $articleRepo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        return new Response(body: $this->twig->render('home.html.twig', [
            'featured_articles' => $this->articleRepo->findFeatured(),
        ]));
    }

    public function getAccessControl(): int {
        return 0;
    }
}