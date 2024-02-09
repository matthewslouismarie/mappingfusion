<?php

require_once './vendor/autoload.php';

use LM\WebFramework\Kernel;

return Kernel::initialize(realpath(dirname(__FILE__)), 'en');