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

    const SEARCH_FORM_NAME = 'search-query';

    public function __construct(
        private ArticleRepository $articleRepository,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo There must be a better way to extract $queryStr.
     */
    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        $uriQuery = $request->getUri()->getQuery();
        $query = '';
        $articles = [];
        if (str_contains($uriQuery, self::SEARCH_FORM_NAME . '=')) {
            $queryStr = urldecode(substr($uriQuery, strlen(self::SEARCH_FORM_NAME . '=')));
            $query = new SearchQuery($queryStr);
            if (count($query->getKeywords()) > 0) {
                $articles = $this->articleRepository->searchArticles($query);
            }
        }
        return new Response(body: $this->twig->render('search_results_list.html.twig', [
            'searchQuery' => $query,
            'articles' => $articles,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}