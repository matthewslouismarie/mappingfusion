<?php

$container = require_once(dirname(__FILE__) . '/../index.php');

use MF\Logging\Logger;
use MF\Test\IUnitTest;

$logger = $container->get(Logger::class);

if (count($argv) < 2) {
    echo("No unit test was passed.\n");
    exit -1;
}

$allFailures = [];
foreach (array_slice($argv, 1) as $unitTestClass) {
    $unitTest = $container->get($unitTestClass);
    if (!$unitTest instanceof IUnitTest) {
        echo("Argument is not a IUnitTest implementation class.\n");
        exit -2;
    }
    $unitTestFailures = $unitTest->run();

    if (count($unitTestFailures) > 0) {
        $allFailures[$unitTestClass] = $unitTestFailures;
    }
}

if (count($allFailures) > 0) {
    echo("Some tests failed.\n");
    foreach ($allFailures as $unitTestClass => $failures) {
        echo("{$unitTestClass} failed!\n");
        foreach ($failures as $failure) {
            echo("{$failure->getTitle()}\n");
            $formattedMessage = str_replace("\n", "\t\n", $failure->getMessage());
            echo("\t{$formattedMessage}\n");
        }
    }
} else {
    echo("Tests completed successfully.\n");
}