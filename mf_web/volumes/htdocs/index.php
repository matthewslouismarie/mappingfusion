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
use MF\Kernel;

const CLI_ID = 'cli';

const ROUTES = [
    '' => HomeController::class,
    'account' => AccountController::class,
    'article' => ArticleController::class,
    'login' => LoginController::class,
    'logout' => LogoutController::class,
    'manage_author' => AuthorController::class,
    'manage_playable' => PlayableController::class,
    'register' => RegistrationController::class,
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
