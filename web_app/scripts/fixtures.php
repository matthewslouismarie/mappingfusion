#!/usr/local/bin/php
<?php

use LM\WebFramework\Configuration\Configuration;
use MF\Cli\Cli;
use MF\Database\DatabaseManager;
use MF\Database\Fixture;
use MF\Logging\Logger;

$container = require_once dirname(__FILE__) . '/../index.php';

$config = $container->get(Configuration::class);
$logger = $container->get(Logger::class);

$cli = new Cli($argv);
$cli->checkIsCli();

$fixtureImagesFolderPath = $config->getPathOfAppDirectory() .'/fixtures/images';
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
        $logger->log("Fixtures loaded.");
        $fixturesLoaded = true;
    } catch (PDOException $e) {
        if ('23000' === $e->getCode()) {
            $container->get(DatabaseManager::class)->getPdo()->rollback();
            $logger->log("Encountered duplicate key exception : {$e->getMessage()}");
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
                $logger->log('Database dropped.');
                $container->get(DatabaseManager::class)->createDatabase();
                $logger->log('New database created.');
            } else {
                $logger->log('Database not dropped.');
                throw $e;
                exit;
            }
        } else {
            throw $e;
        }
    }
}