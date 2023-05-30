<?php

namespace MF;

class Configuration
{
    private array $env;

    public function __construct() {
        $this->env = parse_ini_file(dirname(__FILE__) . '/../.env');
    }

    public function getSetting(string $key): string {
        return $this->env[$key];
    }
}