<?php

namespace MF;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private Environment $twig;

    public function __construct() {
        $loader = new FilesystemLoader('templates');
        $this->twig = new Environment($loader, [
            'cache' => 'cache',
        ]);
    }

    public function getTwig(): Environment {
        return $this->twig;
    }
}