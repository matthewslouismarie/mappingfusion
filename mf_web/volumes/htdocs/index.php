<?php

require_once './vendor/autoload.php';

use DI\ContainerBuilder;
use MF\Kernel;
use MF\RequestFactory;

$container = (new ContainerBuilder())->build();

$requestManager = $container->get(Kernel::class);

$requestManager->manageRequest();