<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Repository\ArticleRepository;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleListController implements ControllerInterface
{
    const ROUTE_ID = 'articles';

    public function __construct(
        private ArticleRepository $repo,
        private CategoryRepository $categoryRepository,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
    ): ResponseInterface {
        if (key_exists(1, $routeParams)) {
            $requestedCategoryId = $routeParams[1];
            $category = $this->categoryRepository->findWithChildren($requestedCategoryId);
            $i = 0;
            $categoryIds = ["cat_{$i}" => $category->id];
            foreach ($category->children as $c) {
                $categoryIds['cat_' . ++$i] = $c->id;
            }
            return new Response(
                body: $this->twig->render('article_list.html.twig', [
                    'articles' => $this->repo->findAllPublished($categoryIds),
                    'categories' => $category->children,
                    'category' => $category,
                ])
            );
        } else {
            return new Response(
                body: $this->twig->render('article_list.html.twig', [
                    'articles' => $this->repo->findAllPublished(),
                    'categories' => $this->categoryRepository->findAll(),
                    'category' => null,
                ])
            );
        }
        
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}