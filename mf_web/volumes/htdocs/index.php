<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use MF\Controller\AccountController;
use MF\Controller\AdminArticleListController;
use MF\Controller\AdminAuthorListController;
use MF\Controller\AdminPlayableListController;
use MF\Controller\AdminReviewListController;
use MF\Controller\ArticleController;
use MF\Controller\AuthorController;
use MF\Controller\CategoryAdminController;
use MF\Controller\CategoryListAdminController;
use MF\Controller\HomeController;
use MF\Controller\LoginController;
use MF\Controller\LogoutController;
use MF\Controller\PlayableController;
use MF\Controller\RegistrationController;
use MF\Controller\ReviewController;
use MF\Kernel;

const CLI_ID = 'cli';

const ROUTES = [
    '' => HomeController::class,
    AccountController::ROUTE_ID=> AccountController::class,
    AdminArticleListController::ROUTE_ID => AdminArticleListController::class,
    AdminAuthorListController::ROUTE_ID => AdminAuthorListController::class,
    AdminPlayableListController::ROUTE_ID => AdminPlayableListController::class,
    AdminReviewListController::ROUTE_ID => AdminReviewListController::class,
    ArticleController::ROUTE_ID => ArticleController::class,
    AuthorController::ROUTE_ID => AuthorController::class,
    CategoryAdminController::ROUTE_ID => CategoryAdminController::class,
    CategoryListAdminController::ROUTE_ID => CategoryListAdminController::class,
    HomeController::ROUTE_ID => HomeController::class,
    LoginController::ROUTE_ID => LoginController::class,
    LogoutController::ROUTE_ID => LogoutController::class,
    PlayableController::ROUTE_ID => PlayableController::class,
    RegistrationController::ROUTE_ID => RegistrationController::class,
    ReviewController::ROUTE_ID => ReviewController::class,
];

$container = (new ContainerBuilder())->build();

if (CLI_ID !== php_sapi_name()) {
    $request = ServerRequest::fromGlobals();
    
    session_start();
    
    $requestManager = $container->get(Kernel::class);
    
    $routeId = isset($request->getQueryParams()['route_id']) ? $request->getQueryParams()['route_id'] : '';
    
    $response = $requestManager->generateResponse($container->get(ROUTES[$routeId]), $request);
    
    if (302 === $response->getStatusCode()) {
        header('Location: ' . $response->getHeaderLine('Location'));
        die();
    } else {
        http_response_code($response->getStatusCode());
        echo $response->getBody()->__toString(); 
    }
}
