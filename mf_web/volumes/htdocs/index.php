<?php

declare(strict_types=1);

set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errNo, $errStr in $errFile on line $errLine";
    if (2 === $errNo) {
        throw new OutOfBoundsException();
    }
    if ($errNo == E_NOTICE || $errNo == E_WARNING) {
        throw new RuntimeException($msg, $errNo);
    } else {
        return false;
    }
});

error_reporting(E_ALL);

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use MF\Controller\AccountController;
use MF\Controller\AdminArticleListController;
use MF\Controller\AdminAuthorListController;
use MF\Controller\AdminContributionController;
use MF\Controller\AdminPlayableListController;
use MF\Controller\AdminReviewListController;
use MF\Controller\AdminArticleController;
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
    AdminArticleController::ROUTE_ID => AdminArticleController::class,
    AdminArticleListController::ROUTE_ID => AdminArticleListController::class,
    AdminAuthorListController::ROUTE_ID => AdminAuthorListController::class,
    AdminContributionController::ROUTE_ID => AdminContributionController::class,
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

if (CLI_ID === php_sapi_name()) {
    return $container;
} else {
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
