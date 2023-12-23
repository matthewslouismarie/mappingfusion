<?php

declare(strict_types=1);

set_error_handler(function ($errNo, $errStr, $errFile, $errLine) {
    $msg = "$errNo, $errStr in $errFile on line $errLine";
    if (2 === $errNo) {
        throw new OutOfBoundsException($msg);
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
use MF\Kernel;

const CLI_ID = 'cli';

$container = (new ContainerBuilder())->build();

if (CLI_ID === php_sapi_name()) {
    return $container;
} else {
    $request = ServerRequest::fromGlobals();
    
    session_start();
    
    $requestManager = $container->get(Kernel::class);

    $routeParams = $requestManager->extractRouteParams($request);

    $routeId = $routeParams[0];
    
    $response = $requestManager->generateResponse($routeId, $request);
    
    if (302 === $response->getStatusCode()) {
        header('Location: ' . $response->getHeaderLine('Location'));
        die();
    } else {
        http_response_code($response->getStatusCode());
        echo $response->getBody()->__toString(); 
    }
}
