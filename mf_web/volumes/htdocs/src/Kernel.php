<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use MF\Controller\AccountController;
use MF\Controller\AdminArticleController;
use MF\Controller\AdminArticleListController;
use MF\Controller\AdminAuthorListController;
use MF\Controller\AdminPlayableController;
use MF\Controller\AdminPlayableListController;
use MF\Controller\AdminReviewListController;
use MF\Controller\AdminTestController;
use MF\Controller\ArticleController;
use MF\Controller\ArticleListController;
use MF\Controller\AdminAuthorController;
use MF\Controller\AdminCategoryController;
use MF\Controller\AdminCategoryListController;
use MF\Controller\HomeController;
use MF\Controller\AdminImageController;
use MF\Controller\LoginController;
use MF\Controller\LogoutController;
use MF\Controller\ProfileController;
use MF\Controller\ReviewController;
use MF\Controller\ReviewListController;
use MF\Controller\SearchController;
use MF\Enum\Clearance;
use MF\Exception\Http\BadRequestException;
use MF\Exception\Http\NotFoundException;
use MF\Session\SessionManager;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class Kernel
{
    const ROUTES = [
        '' => HomeController::class,
        AccountController::ROUTE_ID=> AccountController::class,
        AdminArticleController::ROUTE_ID => AdminArticleController::class,
        AdminArticleListController::ROUTE_ID => AdminArticleListController::class,
        AdminAuthorListController::ROUTE_ID => AdminAuthorListController::class,
        AdminPlayableController::ROUTE_ID => AdminPlayableController::class,
        AdminPlayableListController::ROUTE_ID => AdminPlayableListController::class,
        AdminReviewListController::ROUTE_ID => AdminReviewListController::class,
        AdminTestController::ROUTE_ID => AdminTestController::class,
        ArticleController::ROUTE_ID => ArticleController::class,
        ArticleListController::ROUTE_ID => ArticleListController::class,
        AdminAuthorController::ROUTE_ID => AdminAuthorController::class,
        AdminCategoryController::ROUTE_ID => AdminCategoryController::class,
        AdminCategoryListController::ROUTE_ID => AdminCategoryListController::class,
        HomeController::ROUTE_ID => HomeController::class,
        AdminImageController::ROUTE_ID => AdminImageController::class,
        LoginController::ROUTE_ID => LoginController::class,
        LogoutController::ROUTE_ID => LogoutController::class,
        ProfileController::ROUTE_ID => ProfileController::class,
        // RegistrationController::ROUTE_ID => RegistrationController::class,
        ReviewController::ROUTE_ID => ReviewController::class,
        ReviewListController::ROUTE_ID => ReviewListController::class,
        SearchController::ROUTE_ID => SearchController::class,
    ];

    public function __construct(
        private SessionManager $session,
        private TwigService $twig,
        private ContainerInterface $container,
    ) {
    }

    /**
     * @return array<string>
     */
    public function extractRouteParams(ServerRequestInterface $request): array {
        $parts = array_map(fn ($e) => urldecode($e), explode('/', $request->getUri()->getPath()));
        if (1 === count($parts) && '' === $parts[0]) {
            return $parts;
        } else {
            return array_slice($parts, 1);
        }
    }

    /**
     * @todo Make sure HTTP response is valid and complete.
     */
    public function generateResponse(string $routeId, ServerRequestInterface $request): Response {
        if (!key_exists($routeId, self::ROUTES)) {
            return new Response(
                status: 404,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Cette page n’existe pas.',
                    'title' => 'Page non existante',
                ]),
            );
        }
        $controller = $this->container->get(self::ROUTES[$routeId]);
        if (Clearance::VISITORS === $controller->getAccessControl() && $this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous ne pouvez pas accéder à cette page.',
                    'title' => 'Accès non autorisé',
                ]),
            );
        } elseif (Clearance::ADMINS === $controller->getAccessControl() && !$this->session->isUserLoggedIn()) {
            return new Response(
                status: 403,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Vous devez vous connecter pour accéder à cette page.',
                    'title' => 'Connexion requise',
                ]),
            );
        }

        try {
            return $controller->generateResponse($request, $this->extractRouteParams($request));
        } catch (NotFoundException $e) {
            return new Response(
                status: 404,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Cette page n’existe pas.',
                    'title' => 'Page non existante',
                ]),
            );
        } catch (BadRequestException $e) {
            return new Response(
                status: 400,
                body: $this->twig->render('error.html.twig', [
                    'message' => 'Cette page n’existe pas.',
                    'title' => 'Page non existante',
                ]),
            );
        }
    }
}