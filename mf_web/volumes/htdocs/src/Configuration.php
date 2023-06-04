<?php

namespace MF;

class Configuration
{
    private array $env;

    public function __construct() {
        $this->env = json_decode(file_get_contents(dirname(__FILE__) . '/../.env.json'), true);
    }

    public function getSetting(string $key): string {
        return $this->env[$key];
    }
}