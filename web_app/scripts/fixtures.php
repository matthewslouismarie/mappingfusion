#!/usr/local/bin/php
<?php

$container = require_once dirname(__FILE__) . '/../index.php';

use LM\WebFramework\Configuration\Configuration;
use MF\Cli\Cli;
use MF\Database\DatabaseManager;
use MF\Database\Fixture;
use MF\Logging\Logger;


$config = $container->get(Configuration::class);
$logger = $container->get(Logger::class);

$cli = new Cli($argv);
$cli->checkIsCli();

$fixtureImagesFolderPath = $config->getPathOfAppDirectory() . '/fixtures/images';
$uploadedImagesFolderPath = $config->getPathOfUploadedFiles();

foreach (scandir($fixtureImagesFolderPath) as $filename) {
    if ('.' !== $filename && '..' !== $filename) {
        copy("{$fixtureImagesFolderPath}/{$filename}", "{$uploadedImagesFolderPath}/{$filename}");
    }
}

$fixturesLoaded = false;
while (false === $fixturesLoaded) {
    try {
        $container->get(Fixture::class)->load();
        $logger->logMessage("Fixtures loaded.\n");
        $fixturesLoaded = true;
    } catch (PDOException $e) {
        $container->get(DatabaseManager::class)->getPdo()->rollback();
        $logger->log($e);
        if ('23000' === $e->getCode()) {
            echo("Encountered duplicate key exception : {$e->getMessage()}.\n");
        } else {
            echo("Encountered an error while trying to load the fixtures..\n");
        }
        $drop = false;
        if ($cli->contains('--recreate')) {
            $drop = true;
        } else {
            echo "Drop database? (drop database/N)\n";
            $line = trim(fgets(STDIN));
            if ('drop database' === $line) {
                $drop = true;
            }
        }
        if ($drop) {
            $container->get(DatabaseManager::class)->dropDatabase();
            $logger->logMessage("Database dropped.\n");
            $container->get(DatabaseManager::class)->createDatabase();
            $logger->logMessage("New database created.\n");
        } else {
            $logger->logMessage("Database not dropped.\n");
            throw $e;
            exit;
        }
    }
}