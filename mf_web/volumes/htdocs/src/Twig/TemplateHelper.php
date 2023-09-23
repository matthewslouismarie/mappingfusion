<?php

namespace MF\Twig;

use DateTimeInterface;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Framework\Form\FormFactory;
use MF\Framework\File\FileService;
use MF\Framework\Form\IFormExtractor;
use MF\Session\SessionManager;
use MF\MarkdownService;
use MF\Router;

class TemplateHelper
{
    private IFormExtractor $csrf;

    public function __construct(
        private Configuration $config,
        private MarkdownService $mk,
        private Router $router,
        private SessionManager $session,
        private FileService $file,
    ) {
    }

    public function getAsset(string $filename): string {
        $publicUrl = $this->getPublicUrl();
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "$publicUrl/$filename?version=$version";
    }

    public function getDate(DateTimeInterface $date): string {
        return $date->format('Y-M-D');
    }

    public function getImages(string $text): array {
        $foundImages = [];
        foreach ($this->file->getUploadedImages() as $image) {
            if (str_contains($text, $image)) {
                $foundImages[] = $image;
            }
        }
        return $foundImages;
    }

    public function getImgAttr(string $filename, bool $isResource = true, ?int $width = null, ?int $height = null): string {
        $filePathOnDisk =  realpath(dirname(__FILE__) . '/../../public/' . ($isResource ? 'uploaded/' : '') . $filename);
        $dimensions = getimagesize($filePathOnDisk);

        $srcValue = $isResource ? $this->getResource($filename) : $this->getAsset($filename);
        $attr = "src=\"{$srcValue}\"";

        if ((null === $width || null === $height) && false !== $dimensions) {
            if (null !== $width) {
                $height = $dimensions[1] * $width / $dimensions[0];
            } elseif (null !== $height) {
                $width = $dimensions[0] * $height / $dimensions[1];
            } else {
                $width = $dimensions[0];
                $height = $dimensions[1];
            }
        }

        if (null !== $width && null !== $height) {
            $attr .= " width=\"{$width}px\" height=\"{$height}px\"";
        }

        return $attr;
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

    public function shorten(string $string, int $nCharacters, string $suffix) {
        return mb_substr($string, 0, $nCharacters) . $suffix;
    }
}