<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\Exception\RequestedResourceNotFound;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Repository\ArticleRepository;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ArticleListController implements IController
{
    private ?array $categories = null;

    public function __construct(
        private ArticleRepository $repo,
        private CategoryRepository $categoryRepository,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        try {
            if (key_exists(0, $routeParams)) {
                $requestedCategoryId = $routeParams[0];
                $categories = $this->getCategories();
                if (!key_exists($requestedCategoryId, $categories)) {
                    throw new RequestedResourceNotFound();
                }
                $category = $categories[$requestedCategoryId];
                $articles = $this->repo->findByCategory($requestedCategoryId);
                $onlyReviews = null;
                foreach ($articles as $a) {
                    if (null === $a['review']) {
                        $onlyReviews = false;
                    }
                }
                if (null === $onlyReviews) {
                    $onlyReviews = true;
                }
                return new Response(
                    body: $this->twig->render(
                        'article_list.html.twig',
                        $this->getPage($category),
                        [
                            'articles' => $articles,
                            'childCats' => $this->getCategoryDescendants($categories, $requestedCategoryId),
                            'categories' => $categories,
                            'parentCats' => $this->getCategoryAncestors($categories, $requestedCategoryId),
                            'category' => $category,
                            'onlyReviews' => $onlyReviews,
                        ],
                    ),
                );
            } else {
                return new Response(
                    body: $this->twig->render(
                        'article_list.html.twig',
                        $this->getPage(),
                        [
                            'articles' => $this->repo->findAll(),
                            'categories' => $this->categoryRepository->findAll(),
                            'parentCats' => [],
                            'childCats' => null,
                            'category' => null,
                            'onlyReviews' => false,
                        ],
                    ),
                );
            }
        } catch (EntityNotFoundException $e) {
            throw new RequestedResourceNotFound();
        }
    }

    public function getCategories(): array
    {
        if (null === $this->categories) {
            $this->categories = $this->categoryRepository->findAll();
        }
        return $this->categories;
    }

    public function getPage(null|AppObject|string $pageParam = null): Page
    {
        $category = is_string($pageParam) ? $this->categoryRepository->find($pageParam) : $pageParam;
        $parentCategoryId = $category['parent_id'] ?? null;

        if (null === $category) {
            return $this->pageFactory->create(
                'Liste des articles',
                self::class,
                [],
                HomeController::class,
                function ($parentController) {
                    return $parentController->getPage();
                },
            );
        }
        elseif (null === $parentCategoryId) {
            return $this->pageFactory->create(
                $category['name'],
                self::class,
                [$category['id']],
                self::class,
                function (ArticleListController $parentController) {
                    return $parentController->getPage();
                },
            );
        }
        else {
            /**
             * @todo Optimize, fetch all ancestor categories at once
             */
            return $this->pageFactory->create(
                $category['name'],
                self::class,
                [$category['id']],
                self::class,
                function (ArticleListController $parentController) use ($parentCategoryId) {
                    return $parentController->getPage($parentCategoryId);
                },
            );
        }
        
    }

    private function getCategoryAncestors(array $categories, string $id): array
    {
        $cat = $categories[$id];
        $ancestors = [];
        while (null !== $cat) {
            $ancestors[] = $cat;
            if (null !== $cat['parent_id']) {
                $cat = $categories[$cat['parent_id']];
            } else {
                $cat = null;
            }
        }
        return array_reverse($ancestors);
    }

    private function getCategoryDescendants(array $categories, string $id): array
    {
        $descendants = [];
        foreach ($categories as $cat) {
            if ($cat['parent_id'] == $id) {
                $descendants = array_merge($descendants, [$cat], $this->getCategoryDescendants($categories, $cat['id']));
            }
        }
        return $descendants;
    }
}