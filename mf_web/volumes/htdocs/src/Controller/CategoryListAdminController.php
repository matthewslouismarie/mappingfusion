<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryListAdminController implements ControllerInterface
{
    const ROUTE_ID = 'admin_category_list';

    private CategoryRepository $repo;

    private TwigService $twig;

    public function __construct(
        CategoryRepository $repo,
        TwigService $twig,
    ) {
        $this->repo = $repo;
        $this->twig = $twig;
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_category_list.html.twig', [
            'categories' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): int {
        return 1;
    }
}