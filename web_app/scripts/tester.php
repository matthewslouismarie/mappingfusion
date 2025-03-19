<?php

use MF\Logging\Logger;
use MF\Test\IUnitTest;

$container = require_once(dirname(__FILE__) . '/../index.php');

$logger = $container->get(Logger::class);

if (count($argv) < 2) {
    echo('No unit test was passed.');
    exit -1;
}

$allFailures = [];
foreach (array_slice($argv, 1) as $unitTestClass) {
    $unitTest = $container->get($unitTestClass);
    if (!$unitTest instanceof IUnitTest) {
        echo('Argument is not a IUnitTest implementation class.');
        exit -2;
    }
    $unitTestFailures = $unitTest->run();

    if (count($unitTestFailures) > 0) {
        $allFailures[$unitTestClass] = $unitTestFailures;
    }
}

if (count($allFailures) > 0) {
    echo('Some tests failed');
    foreach ($allFailures as $unitTestClass => $failures) {
        echo("{$unitTestClass} failed!");
        foreach ($failures as $failure) {
            echo($failure->getTitle());
            echo("\t" . str_replace("\n", "\t\n", $failure->getMessage()));
        }
    }
} else {
    echo('Tests completed successfully.');
}