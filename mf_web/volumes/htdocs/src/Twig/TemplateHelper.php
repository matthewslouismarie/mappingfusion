<?php

namespace MF\Twig;

use MF\Configuration;
use MF\Enum\LinkType;
use MF\HttpBridge\Session;
use MF\MarkdownService;
use MF\Router;

class TemplateHelper
{
    public function __construct(
        private Configuration $config,
        private MarkdownService $mk,
        private Router $router,
        private Session $session,
    ) {
    }

    function getAsset(string $filename): string {
        $publicUrl = $this->getPublicUrl();
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "$publicUrl/$filename?version=$version";
    }

    public function getLinkTypes(): array {
        return LinkType::cases();
    }

    public function getMk(): MarkdownService {
        return $this->mk;
    }

    public function getPublicUrl(): string {
        return $this->config->getSetting('publicUrl');
    }

    function getResource(string $filename): string {
        $publicUrl = $this->getPublicUrl();
        return "$publicUrl/uploaded/$filename";
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getSession(): Session {
        return $this->session;
    }
}