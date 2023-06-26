<?php

use MF\Controller\HomeController;
use MF\Router;
use MF\Twig\TemplateHelper;

$container = require_once(dirname(__FILE__) . '/../index.php');

$router = $container->get(Router::class);
$tplHelper = $container->get(TemplateHelper::class);

$urls = [
    $router->generateUrl(HomeController::ROUTE_ID),
    $tplHelper->getAsset('bullsquid-transparent.svg'),
    $tplHelper->getAsset('style.css'),
    $tplHelper->getResource('202111271344571.jpg'),
];

$context = stream_context_create(['http' => ['ignore_errors' => true]]);

foreach ($urls as $url) {
    echo "Fetching $url...\n";
    $home = file_get_contents('http://localhost' . $url);
    
    $httpResponseCode = $http_response_header[0];
    if (!str_contains($httpResponseCode, '200')) {
        echo "HTTP response code is $httpResponseCode, should contain 200 OK.";
        exit(1);
    }
}