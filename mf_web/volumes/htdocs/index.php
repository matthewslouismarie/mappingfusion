<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use MF\Controller\AccountController;
use MF\Controller\ArticleController;
use MF\Controller\AuthorController;
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
    HomeController::ROUTE_ID => HomeController::class,
    AccountController::ROUTE_ID=> AccountController::class,
    ArticleController::ROUTE_ID => ArticleController::class,
    LoginController::ROUTE_ID => LoginController::class,
    LogoutController::ROUTE_ID => LogoutController::class,
    AuthorController::ROUTE_ID => AuthorController::class,
    PlayableController::ROUTE_ID => PlayableController::class,
    ReviewController::ROUTE_ID => ReviewController::class,
    RegistrationController::ROUTE_ID => RegistrationController::class,
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
