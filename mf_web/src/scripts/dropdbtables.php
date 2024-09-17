<?php

use LM\WebFramework\Configuration;
use MF\Database\DatabaseManager;

$container = require_once dirname(__FILE__) . '/../index.php';

$connection = $container->get(DatabaseManager::class);
$dbName = $container->get(Configuration::class)->getSetting('dbName');

$connection->run('DROP TABLE e_article;');
$connection->run('DROP TABLE e_contribution;');
$connection->run('DROP TABLE e_category;');
$connection->run('DROP TABLE e_member;');
$connection->run('DROP TABLE e_playable_link;');
$connection->run('DROP TABLE e_review;');
$connection->run('DROP TABLE e_playable;');
$connection->run('DROP VIEW v_article;');
$connection->run('DROP VIEW v_playable;');
$connection->run('DROP TABLE e_author;');