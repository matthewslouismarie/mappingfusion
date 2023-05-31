<?php

namespace MF;

use MF\HttpBridge\Session;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private Environment $twig;

    private Session $session;

    public function __construct(
        Configuration $config,
        Session $session,
    ) {
        $loader = new FilesystemLoader('templates');
        $this->session = $session;
        $this->twig = new Environment($loader, [
            'cache' => $config->getSetting('dev') ? false : 'cache',
        ]);
    }

    public function render(string $filename, array $parameters = []): string {
        return $this->twig->load($filename)->render($parameters + [
            'app' => [
                'stylesheetVersion' => filemtime(dirname(__FILE__) . '/../public/style.css'),
            ],
            'session' => $this->session,
        ]);
    }

    public function getTwig(): Environment {
        return $this->twig;
    }
}