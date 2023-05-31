<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use MF\Kernel;

session_start();

$container = (new ContainerBuilder())->build();

$requestManager = $container->get(Kernel::class);

$requestManager->manageRequest();