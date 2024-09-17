<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\Controller\SinglePageOwner;
use LM\WebFramework\DataStructures\Page;
use MF\Repository\ArticleRepository;
use MF\Repository\CategoryRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController implements IController, SinglePageOwner
{
    public function __construct(
        private ArticleRepository $articleRepo,
        private CategoryRepository $catRepo,
        private Configuration $config,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(body: $this->twig->render(
            'home.html.twig',
            $this->getPage(),
            [
                'featured_articles' => $this->articleRepo->findFeatured(),
                'reviews' => $this->articleRepo->findAllReviews(),
                'last_articles' => $this->articleRepo->findLastArticles(),
                'last_reviews' => $this->articleRepo->findLastReviews(),
                'other_articles' => $this->catRepo->find($this->config->getSetting('otherCategoryId')),
            ],
        ));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }

    public function getPage(): Page
    {
        return new Page(
            null,
            'Mapping-Fusion',
            $this->config->getHomeUrl(),
        );
    }
}