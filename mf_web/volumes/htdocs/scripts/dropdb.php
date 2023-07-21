<?php

use MF\Configuration;
use MF\Database\DatabaseManager;

$container = require_once dirname(__FILE__) . '/../index.php';

$connection = $container->get(DatabaseManager::class);
$dbName = $container->get(Configuration::class)->getSetting('DB_NAME');

$connection->getPdo()->exec("DROP DATABASE {$dbName};");