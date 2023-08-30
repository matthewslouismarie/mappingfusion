<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use MF\Controller\AccountController;
use MF\Controller\AdminArticleController;
use MF\Controller\AdminArticleListController;
use MF\Controller\AdminAuthorListController;
use MF\Controller\AdminContributionController;
use MF\Controller\AdminPlayableController;
use MF\Controller\AdminPlayableListController;
use MF\Controller\AdminReviewListController;
use MF\Controller\AdminTestController;
use MF\Controller\ArticleController;
use MF\Controller\ArticleListController;
use MF\Controller\AuthorController;
use MF\Controller\CategoryAdminController;
use MF\Controller\CategoryListAdminController;
use MF\Controller\ControllerInterface;
use MF\Controller\HomeController;
use MF\Controller\ImageManagementController;
use MF\Controller\LoginController;
use MF\Controller\LogoutController;
use MF\Controller\PlayableController;
use MF\Controller\RegistrationController;
use MF\Controller\ReviewController;
use MF\Controller\ReviewListController;
use MF\Controller\SearchController;
use MF\Enum\Clearance;
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
        AdminContributionController::ROUTE_ID => AdminContributionController::class,
        AdminPlayableController::ROUTE_ID => AdminPlayableController::class,
        AdminPlayableListController::ROUTE_ID => AdminPlayableListController::class,
        AdminReviewListController::ROUTE_ID => AdminReviewListController::class,
        AdminTestController::ROUTE_ID => AdminTestController::class,
        ArticleController::ROUTE_ID => ArticleController::class,
        ArticleListController::ROUTE_ID => ArticleListController::class,
        AuthorController::ROUTE_ID => AuthorController::class,
        CategoryAdminController::ROUTE_ID => CategoryAdminController::class,
        CategoryListAdminController::ROUTE_ID => CategoryListAdminController::class,
        HomeController::ROUTE_ID => HomeController::class,
        ImageManagementController::ROUTE_ID => ImageManagementController::class,
        LoginController::ROUTE_ID => LoginController::class,
        LogoutController::ROUTE_ID => LogoutController::class,
        PlayableController::ROUTE_ID => PlayableController::class,
        RegistrationController::ROUTE_ID => RegistrationController::class,
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

    public function extractRouteParams(ServerRequestInterface $request): array {
        return explode('/', $request->getQueryParams()['route_params']);
    }

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
        return $controller->generateResponse($request, $this->extractRouteParams($request));
    }
}