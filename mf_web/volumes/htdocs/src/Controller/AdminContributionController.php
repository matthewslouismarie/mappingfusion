<?php

namespace MF\Controller;

use Exception;
use MF\Enum\Clearance;
use MF\Repository\AuthorRepository;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @todo Restore.
 */
class AdminContributionController implements ControllerInterface
{
    const ROUTE_ID = 'add_contribution';

    public function __construct(
        private AuthorRepository $repoAuthors,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        throw new Exception();
    }

    public function getAccessControl(): Clearance {
        return Clearance::ADMINS;
    }
}