<?php

namespace MF\Controller;

use GuzzleHttp\Psr7\Response;
use LM\WebFramework\Controller\IResponseGenerator;
use LM\WebFramework\DataStructures\Page;
use MF\Router;
use MF\TwigService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorPageNotFoundController implements IResponseGenerator
{
    public function __construct(
        private PageFactory $pageFactory,
        private Router $router,
        private TwigService $twig,
    ) {
    }

    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface {
        return new Response(
            status: 404,
            body: $this->twig->render(
                'errors/error_page.html.twig',
                $this->getPage($request),
                [
                    'message' => 'Cette page n’existe pas… :(',
                    'title' => 'Page non trouvée',
                ],
            ),
        );
    }

    /**
     * @todo Maybe, error pages shouldn’t exist?
     */
    public function getPage(ServerRequestInterface $request): Page {
        $path = $this->router->getRequestUrl($request);
        
        return $this->pageFactory->createFromUri(
            'Page non existante',
            $path,
            HomeController::class,
            function (HomeController $homeController) {
                return $homeController->getPage();
            },
            isIndexed: false,
            isPartOfHierarchy: false,
        );
    }
}