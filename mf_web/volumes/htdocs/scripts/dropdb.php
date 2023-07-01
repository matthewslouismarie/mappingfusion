<?php

use MF\Configuration;
use MF\Database\Connection;

$container = require_once dirname(__FILE__) . '/../index.php';

$connection = $container->get(Connection::class);
$dbName = $container->get(Configuration::class)->getSetting('DB_NAME');

$connection->getPdo()->exec("DROP DATABASE {$dbName};");