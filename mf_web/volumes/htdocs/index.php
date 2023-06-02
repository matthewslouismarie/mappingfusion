<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use MF\Controller\AccountController;
use MF\Controller\ArticleController;
use MF\Controller\HomeController;
use MF\Controller\LoginController;
use MF\Controller\LogoutController;
use MF\Controller\RegistrationController;
use MF\Kernel;

session_start();

$container = (new ContainerBuilder())->build();

$request = ServerRequest::fromGlobals();

$requestManager = $container->get(Kernel::class);

const ROUTES = [
    '' => HomeController::class,
    'account' => AccountController::class,
    'article' => ArticleController::class,
    'login' => LoginController::class,
    'logout' => LogoutController::class,
    'register' => RegistrationController::class,
];

$routeId = isset($request->getQueryParams()['route_id']) ? $request->getQueryParams()['route_id'] : '';

$response = $requestManager->generateResponse($container->get(ROUTES[$request->getQueryParams()['route_id']]), $request);

if (302 === $response->getStatusCode()) {
    header('Location: ' . $response->getHeaderLine('Location'));
    die();
} else {
    http_response_code($response->getStatusCode());
    echo $response->getBody()->__toString(); 
}