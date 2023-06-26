<?php

use MF\Database\Fixture;

$container = require_once '../index.php';

$container->get(Fixture::class)->load();