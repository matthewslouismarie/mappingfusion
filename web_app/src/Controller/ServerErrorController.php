<?php

namespace MF\Controller;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\Controller\IController;
use LM\WebFramework\DataStructures\Page;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerErrorController implements IController
{
    private ?Page $page = null;

    public function __construct(
        private Configuration $configuration,
        private TwigService $twigService,
        private HomeController $home,
    ) {
    }

    public function generateResponse(
        ServerRequestInterface $request,
        array $routeParams,
        array $serverParams,
    ): ResponseInterface {
        $time = time();
        $message = "Probablement pas mais j’ai quelques écarts anormaux sur… ({$serverParams['throwable_hash']} vers {$time})";
    
        return $this->twigService->respond(
            'errors/error_page.html.twig',
            $this->getPage("Ce n’est probablement pas un problème…", $request->getRequestTarget()),
            [
                'message' => $message,
            ],
            500,
        );
    }

    public function getPage(string $title, string $requestedUrl): Page
    {
        if (null === $this->page) {
            $this->page = new Page(
                $this->home->getPage(),
                $title,
                $requestedUrl,
                false,
                false,
            );
        }
        return $this->page;
    }
}