<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\AccessControl\Clearance;
use LM\WebFramework\Configuration;
use LM\WebFramework\Controller\ControllerInterface;
use LM\WebFramework\DataStructures\Page;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerErrorController implements ControllerInterface
{
    private ?Page $page;

    public function __construct(
        private Configuration $configuration,
        private PageFactory $pageFactory,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        $body = $this->configuration->isDev() ? $routeParams[count($routeParams) - 1]->__toString() : 'Ce n’est probablement pas un problème… probablement pas mais j’ai quelques écarts anormaux sur…';
    
        return new Response(
            status: 500,
            body: $body,
        );
    }

    public function getAccessControl(): Clearance
    {
        return Clearance::ALL;
    }

    public function getPage(array $pageParams): Page {
        if (null === $this->page) {
            $this->page = $this->pageFactory->createPage(
                'Erreur serveur',
                self::class,
            );
        }
        return $this->page;
    }
}