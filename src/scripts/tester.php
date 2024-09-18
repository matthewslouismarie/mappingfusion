<?php

use LM\WebFramework\Logging\Logger;
use MF\Test\IUnitTest;

$container = require_once(dirname(__FILE__) . '/../index.php');

$logger = $container->get(Logger::class);

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
        $allFailures[$unitTestClass] = $unitTestFailures;
    }
}

if (count($allFailures) > 0) {
    $logger->log('Some tests failed');
    foreach ($allFailures as $unitTestClass => $failures) {
        $logger->log("{$unitTestClass} failed!");
        foreach ($failures as $failure) {
            $logger->log($failure->getTitle());
            $logger->log("\t" . str_replace("\n", "\t\n", $failure->getMessage()));
        }
    }
} else {
    $logger->log('Tests completed successfully.');
}