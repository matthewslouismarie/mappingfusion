<?php

use MF\Database\Fixture;

$container = require_once dirname(__FILE__) . '/../index.php';

$container->get(Fixture::class)->load();