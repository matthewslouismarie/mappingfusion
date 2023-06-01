<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use GuzzleHttp\Psr7\ServerRequest;
use MF\Kernel;

session_start();

$container = (new ContainerBuilder())->build();

$request = ServerRequest::fromGlobals();

$requestManager = $container->get(Kernel::class);

$requestManager->manageRequest($request);