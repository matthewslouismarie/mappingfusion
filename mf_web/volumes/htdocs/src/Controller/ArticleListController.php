<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use MF\Exception\Database\EntityNotFoundException;
use MF\Repository\ArticleRepository;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleListController implements ControllerInterface
{
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
        try {
            if (key_exists(1, $routeParams)) {
                $requestedCategoryId = $routeParams[1];
                $categories = $this->categoryRepository->findAll();
                if (!key_exists($requestedCategoryId, $categories)) {
                    throw new RequestedResourceNotFound();
                }
                $category = $categories[$requestedCategoryId];
                $articles = $this->repo->findByCategory($requestedCategoryId);
                $onlyReviews = null;
                foreach ($articles as $a) {
                    if (null === $a->review) {
                        $onlyReviews = false;
                    }
                }
                if (null === $onlyReviews) {
                    $onlyReviews = true;
                }
                return new Response(
                    body: $this->twig->render('article_list.html.twig', [
                        'articles' => $articles,
                        'categories' => $this->getDescendants($categories, $requestedCategoryId),
                        'parentCats' => $this->getAncestors($categories, $requestedCategoryId),
                        'category' => $category,
                        'onlyReviews' => $onlyReviews,
                    ])
                );
            } else {
                return new Response(
                    body: $this->twig->render('article_list.html.twig', [
                        'articles' => $this->repo->findAll(true),
                        'categories' => $this->categoryRepository->findAll(),
                        'parentCats' => [],
                        'category' => null,
                        'onlyReviews' => false,
                    ])
                );
            }
        } catch (EntityNotFoundException $e) {
            throw new RequestedResourceNotFound();
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }

    private function getAncestors(array $categories, string $id): array {
        $cat = $categories[$id];
        $ancestors = [];
        while (null !== $cat) {
            $ancestors[] = $cat;
            if (null !== $cat->parentId) {
                $cat = $categories[$cat->parentId];
            } else {
                $cat = null;
            }
        }
        return array_reverse($ancestors);
    }

    private function getDescendants(array $categories, string $id): array {
        $descendants = [];
        foreach ($categories as $cat) {
            if ($cat->parentId == $id) {
                $descendants = array_merge($descendants, [$cat], $this->getDescendants($categories, $cat->id));
            }
        }
        return $descendants;
    }
}