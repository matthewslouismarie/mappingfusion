<?php

use MF\Framework\Logging\Logger;
use MF\Framework\Test\IUnitTest;

$container = require_once(dirname(__FILE__) . '/../index.php');

$logger = new Logger('Tester');

if (count($argv) < 2) {
    $logger->log('No unit test was passed.');
    exit -1;
}

$allFailures = [];
foreach (array_slice($argv, 1) as $unitTestClass) {
    $unitTest = $container->get($unitTestClass);
    if (!$unitTest instanceof IUnitTest) {
        $logger->log('Argument is not a IUnitTest implementation class.');
        exit -2;
    }
    $unitTestFailures = $unitTest->run();
    if (count($unitTestFailures) > 0) {
        $allFailures[] = $unitTestFailures;
    }
}
