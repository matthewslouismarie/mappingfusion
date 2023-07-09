<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use MF\Enum\Clearance;
use MF\Model\Contribution;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AdminContributionController implements ControllerInterface
{
    const ROUTE_ID = 'add_contribution';

    public function __construct(
        private AuthorRepository $repoAuthors,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request): ResponseInterface {
        $contribution = $this->getContributionFromRequest($request);
        return new Response(body: $this->twig->render('contribution_form.html.twig', [
            'contribution' => $contribution?->toArray(),
            'playableId' => $request->getQueryParams()['contribution_playable_id'],
            'authors' => $this->repoAuthors->findAll(),
        ]));
    }

    private function getContributionFromRequest(ServerRequestInterface $request): ?Contribution {
        if ('POST' === $request->getMethod()) {
            return Contribution::fromArray($request->getParsedBody());
        } else {
            return null;
        }
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}