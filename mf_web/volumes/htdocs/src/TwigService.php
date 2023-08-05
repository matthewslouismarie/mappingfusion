<?php

namespace MF;

use InvalidArgumentException;
use MF\Twig\TemplateHelper;
use Twig\Environment;
use Twig\Extension\DebugExtension;
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
        $dev = $twigConfig->getSetting('dev');
        $this->twig = new Environment($twigLoader, [
            'debug' => $dev ? true : false,
            'cache' => $dev ? false : 'cache',
            'strict_variables' => true,
        ]);
        if ($dev) {
            $this->twig->addExtension(new DebugExtension());
        }
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