<?php

namespace MF\Controller;
use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Framework\DataStructures\SearchQuery;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class SearchController implements ControllerInterface
{
    const ROUTE_ID = 'recherche';

    public function __construct(
        private ArticleRepository $articleRepository,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo There must be a better way to extract $queryStr.
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        $queryStr = substr($routeParams[1], strlen('search-query'));
        $query = new SearchQuery($queryStr);
        $articles = $this->articleRepository->searchArticles($query);
        return new Response(body: $this->twig->render('search_results_list.html.twig', [
            'searchQuery' => $query,
            'articles' => $articles,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}