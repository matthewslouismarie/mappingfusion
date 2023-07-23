<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
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

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {    
        return new Response(body: $this->twig->render('admin_category_list.html.twig', [
            'categories' => $this->repo->findAll(),
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}