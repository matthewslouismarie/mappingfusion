<?php

namespace MF\Twig;

use MF\HttpBridge\Session;
use MF\MarkdownService;
use MF\Router;

class TemplateHelper
{
    public function __construct(
        private MarkdownService $mk,
        private Router $router,
        private Session $session,
    ) {
    }

    public function getMk(): MarkdownService {
        return $this->mk;
    }

    function getResource(string $filename): string {
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "/public/$filename?version=$version";
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getSession(): Session {
        return $this->session;
    }
}