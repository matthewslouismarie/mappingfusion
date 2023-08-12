<?php

namespace MF\Twig;

use MF\Configuration;
use MF\Enum\LinkType;
use MF\Form\FormFactory;
use MF\Form\StdFormElement;
use MF\Form\Submittable;
use MF\Form\Transformer\CsrfTransformer;
use MF\Session\SessionManager;
use MF\MarkdownService;
use MF\Router;

class TemplateHelper
{
    private Submittable $csrf;

    public function __construct(
        private Configuration $config,
        private MarkdownService $mk,
        private Router $router,
        private SessionManager $session,
        private FormFactory $formFactory,
    ) {
    }

    public function getAsset(string $filename): string {
        $publicUrl = $this->getPublicUrl();
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "$publicUrl/$filename?version=$version";
    }

    public function getCsrf(): Submittable {
        return $this->formFactory->getCsrfFormElement();
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

    public function getResource(string $filename): string {
        $publicUrl = $this->getPublicUrl();
        return "$publicUrl/uploaded/$filename";
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getSession(): SessionManager {
        return $this->session;
    }

    public function isDev(): bool {
        return $this->config->getBoolSetting('dev');
    }
}