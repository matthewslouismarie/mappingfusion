<?php

use LM\WebFramework\Configuration;
use MF\Database\DatabaseManager;
use MF\Database\Fixture;

$container = require_once dirname(__FILE__) . '/../index.php';

try {
    $config = $container->get(Configuration::class);
    $fixtureImagesFolderPath = $config->getPathOfAppDirectory() .'/fixtures/images';
    $uploadedImagesFolderPath = $config->getPathOfUploadedFiles();

    foreach (scandir($fixtureImagesFolderPath) as $filename) {
        if ('.' !== $filename && '..' !== $filename) {
            copy("{$fixtureImagesFolderPath}/{$filename}", "{$uploadedImagesFolderPath}/{$filename}");
        }
    }

    $container->get(Fixture::class)->load();
} catch (PDOException $e) {
    if ('23000' === $e->getCode()) {
        echo "Encountered duplicate key exception : {$e->getMessage()}\n";
        echo "Drop database? (drop database/N)\n";
        $line = trim(fgets(STDIN));
        if ('drop database' === $line) {
            $container->get(DatabaseManager::class)->dropDatabase();
            echo "Database dropped.\n";
            exit;
        }
    }
    throw $e;
}