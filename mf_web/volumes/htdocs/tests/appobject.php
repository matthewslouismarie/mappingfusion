<?php

use MF\Test\Tester;

$container = require_once(dirname(__FILE__) . '/../index.php');

$tester = $container->get(Tester::class);