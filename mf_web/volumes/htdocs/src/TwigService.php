<?php

namespace MF;

use MF\HttpBridge\Session;
use MF\Router;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private Environment $twig;

    private MarkdownService $mk;

    private Router $router;

    private Session $session;

    public function __construct(
        Configuration $config,
        MarkdownService $mk,
        Router $router,
        Session $session,
    ) {
        $this->mk = $mk;
        $this->router = $router;
        $this->session = $session;
        $loader = new FilesystemLoader('templates');
        $this->twig = new Environment($loader, [
            'debug' => $config->getSetting('dev') ? true : false,
            'cache' => $config->getSetting('dev') ? false : 'cache',
            'strict_variables' => true,
        ]);
    }

    public function render(string $filename, array $parameters = []): string {
        return $this->twig->load($filename)->render($parameters + [
            'app' => [
                'mk' => $this->mk,
                'router' => $this->router,
                'stylesheetVersion' => filemtime(dirname(__FILE__) . '/../public/style.css'),
            ],
            'session' => $this->session,
        ]);
    }

    public function getTwig(): Environment {
        return $this->twig;
    }
}