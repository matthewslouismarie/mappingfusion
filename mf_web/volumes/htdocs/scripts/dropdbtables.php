<?php

use MF\Configuration;
use MF\Database\DatabaseManager;

$container = require_once dirname(__FILE__) . '/../index.php';

$connection = $container->get(DatabaseManager::class);
$dbName = $container->get(Configuration::class)->getSetting('DB_NAME');

$connection->getPdo()->exec("DROP TABLE e_article;");
$connection->getPdo()->exec("DROP TABLE e_contribution;");
$connection->getPdo()->exec("DROP TABLE e_author;");
$connection->getPdo()->exec("DROP TABLE e_category;");
$connection->getPdo()->exec("DROP TABLE e_member;");
$connection->getPdo()->exec("DROP TABLE e_playable_link;");
$connection->getPdo()->exec("DROP TABLE e_review;");
$connection->getPdo()->exec("DROP TABLE e_playable;");
$connection->getPdo()->exec("DROP VIEW v_article;");
$connection->getPdo()->exec("DROP VIEW v_playable;");