<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\Page;
use LM\WebFramework\DataStructures\SearchQuery;
use MF\Repository\ArticleRepository;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class SearchController implements IController
{
    const SEARCH_FORM_NAME = 'search-query';

    public function __construct(
        private ArticleRepository $articleRepository,
        private PageFactory $pageFactory,
        private TwigService $twig,
    ) {
    }

    /**
     * @todo There must be a better way to extract $queryStr.
     */
    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): Response {
        $uriQuery = $request->getUri()->getQuery();
        $query = '';
        $articles = [];
        $queryStr = null;
        if (str_contains($uriQuery, self::SEARCH_FORM_NAME . '=')) {
            $queryStr = urldecode(substr($uriQuery, strlen(self::SEARCH_FORM_NAME . '=')));
            $query = new SearchQuery($queryStr);
            if (count($query->getKeywords()) > 0) {
                $articles = $this->articleRepository->searchArticles($query);
            }
        }
        return $this->twig->respond(
            'search_results_list.html.twig',
            $this->getPage($queryStr),
            [
                'searchQuery' => $query,
                'articles' => $articles,
            ],
        );
    }

    public function getPage(?string $queryStr): Page
    {
        $blankSearch = null === $queryStr || '' === $queryStr;
        return $this->pageFactory->createPage(
            $blankSearch ? "Recherche de « {$queryStr} »" : 'Recherche',
            self::class,
            $blankSearch ? [] : [$queryStr],
            HomeController::class,
            isIndexed: false,
            isPartOfHierarchy: false,
        );
    }
}