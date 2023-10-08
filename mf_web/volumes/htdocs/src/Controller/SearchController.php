<?php

namespace MF\Controller;
use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\TwigService;
use Psr\Http\Message\ServerRequestInterface;

class SearchController implements ControllerInterface
{
    const ROUTE_ID = 'recherche';

    public function __construct(
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): Response {
        $query = $request->getParsedBody()['search-query'] ?? null;
        return new Response(body: $this->twig->render('search_results_list.html.twig', [
            'searchQuery' => $query,
        ]));
    }

    public function getAccessControl(): Clearance {
        return Clearance::ALL;
    }
}