<?php

namespace MF\Controller;

use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Controller\IController;
use MF\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class AdminArticleChapterIndexController implements IController
{
    public function __construct(
        private Router $router,
    ) {
    }
    public function getAccessControl(): Clearance
    {
        return Clearance::ADMINS;
    }
    
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface
    {
        $articleId = $this->extractArticleIdFromRequest($request);
        
    }

    private function extractArticleIdFromRequest(ServerRequestInterface $request): string
    {
        return $this->router->getRouteParams($request)[1];
    }
}