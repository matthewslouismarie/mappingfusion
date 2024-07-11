<?php

namespace MF;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use LM\WebFramework\Configuration;
use LM\WebFramework\DataStructures\Page;
use MF\Twig\TemplateHelper;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    private TemplateHelper $templateHelper;

    private Environment $twig;

    public function __construct(
        TemplateHelper $templateHelper,
        Configuration $configuration,
    ) {
        $this->templateHelper = $templateHelper;
        $twigLoader = new FilesystemLoader($configuration->getPathOfProjectDirectory() . '/templates');
        $dev = $configuration->getSetting('dev');
        $this->twig = new Environment($twigLoader, [
            'debug' => $dev ? true : false,
            'cache' => $dev ? false : 'cache',
            'strict_variables' => true,
        ]);
        if ($dev) {
            $this->twig->addExtension(new DebugExtension());
        }
    }

    public function render(string $filename, Page $page, array $parameters = []): string {
        if (isset($parameters['app'])) {
            throw new InvalidArgumentException();
        }

        return $this->twig->load($filename)->render($parameters + [
            'page' => $page,
            'app' => $this->templateHelper,
        ]);
    }

    public function respond(string $filename, Page $page, array $parameters = [], int $statusCode = 200): ResponseInterface {
        return new Response(
            body: $this->render($filename, $page, $parameters),
            status: $statusCode,
        );
    }

    public function getTwig(): Environment {
        return $this->twig;
    }
}