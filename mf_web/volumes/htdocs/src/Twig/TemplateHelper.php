<?php

namespace MF\Twig;

use DateTimeInterface;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Framework\DataStructures\Filename;
use MF\Framework\Form\FormFactory;
use MF\Framework\File\FileService;
use MF\Framework\Form\IFormExtractor;
use MF\Session\SessionManager;
use MF\MarkdownService;
use MF\Router;
use UnexpectedValueException;

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

    public function getHomeUrl(): string {
        return $this->config->getSetting('homeUrl');
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

    public function getImgAttr(string $alt, string $filename, bool $isResource = true, ?int $width = null, ?int $height = null, bool $smallImg = false): string {

        $srcValue = $isResource ? ($smallImg ? $this->getSmallImage($filename) : $this->getResource($filename)) : $this->getAsset($filename);
        $attr = "alt=\"{$alt}\" src=\"{$srcValue}\"";

        $filePathOnDisk =  realpath(dirname(__FILE__) . '/../../public/' . ($isResource ? 'uploaded/' : '') . $filename);
        if (false !== $filePathOnDisk) {
            $dimensions = getimagesize($filePathOnDisk);
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
        }

        if (null !== $width && null !== $height) {
            $attr .= " width=\"{$width}px\" height=\"{$height}px\"";
        }

        return $attr;
    }

    public function getItemId(string $url = ''): string {
        return 'mappingfusion.fr';
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
    
    public function getSmallImage(string $filename): string {
        $filenameObject = new Filename($filename);
        $fileExtension = $filenameObject->getExtension();
        $filenameNoExtension = $filenameObject->getFilenameNoExtension();
        $publicUrl = $this->getPublicUrl();
        return "$publicUrl/uploaded/$filenameNoExtension.small.$fileExtension";
    }
    
    public function getMediumImage(string $filename): string {
        $filenameObject = new Filename($filename);
        $fileExtension = $filenameObject->getExtension();
        $filenameNoExtension = $filenameObject->getFilenameNoExtension();
        $publicUrl = $this->getPublicUrl();
        return "$publicUrl/uploaded/$filenameNoExtension.medium.$fileExtension";
    }

    public function isDev(): bool {
        return $this->config->getBoolSetting('dev');
    }

    public function shorten(string $string, int $nCharacters, string $suffix) {
        return mb_substr($string, 0, $nCharacters) . $suffix;
    }
}