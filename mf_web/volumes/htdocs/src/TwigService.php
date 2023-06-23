<?php

namespace MF;

use InvalidArgumentException;
use MF\Twig\TemplateHelper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private TemplateHelper $templateHelper;

    private Environment $twig;

    public function __construct(
        TemplateHelper $templateHelper,
        Configuration $twigConfig,
    ) {
        $this->templateHelper = $templateHelper;
        $twigLoader = new FilesystemLoader('templates');
        $this->twig = new Environment($twigLoader, [
            'debug' => $twigConfig->getSetting('dev') ? true : false,
            'cache' => $twigConfig->getSetting('dev') ? false : 'cache',
            'strict_variables' => true,
        ]);
    }

    public function render(string $filename, array $parameters = []): string {
        if (isset($parameters['app'])) {
            throw new InvalidArgumentException();
        }

        return $this->twig->load($filename)->render($parameters + [
            'app' => $this->templateHelper,
        ]);
    }

    public function getTwig(): Environment {
        return $this->twig;
    }
}