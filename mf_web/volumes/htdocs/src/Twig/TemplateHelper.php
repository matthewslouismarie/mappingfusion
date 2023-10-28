<?php

namespace MF\Twig;

use DateTimeInterface;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Framework\DataStructures\Filename;
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
        private FileService $fileService,
        private MarkdownService $mk,
        private Router $router,
        private SessionManager $session,
    ) {
    }

    public function estimateReadingTime(string $text): int {
        return round(str_word_count($text) / 238);
    }

    public function getArticleItemId(string $id): string {
        return $this->getItemID() . "/article/{$id}#article";
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

        preg_match_all('/(?<=<!---img )(.+)(?= -->)/', $text, $foundImages);

        return $foundImages[0];
    }

    public function getImgAttr(string $alt, string $filename, bool $isResource = true, ?int $width = null, ?int $height = null, bool $smallImg = false): string {

        $srcValue = $isResource ? ($smallImg ? $this->getSmallImage($filename) : $this->getResource($filename)) : $this->getAsset($filename);
        $attr = "alt=\"{$alt}\" src=\"{$srcValue}\"";

        $filePathOnDisk =  $isResource ? $this->getPathOfResource($filename) : $this->getPathOfPublicFile($filename);
        if (false !== $filePathOnDisk) {
            $dimensions = getimagesize($filePathOnDisk);
            if ((null === $width || null === $height) && false !== $dimensions) {
                if (null !== $width) {
                    $height = round($dimensions[1] * $width / $dimensions[0]);
                } elseif (null !== $height) {
                    $width = round($dimensions[0] * $height / $dimensions[1]);
                } else {
                    $width = $dimensions[0];
                    $height = $dimensions[1];
                }
            }
        }

        if (null !== $width && null !== $height) {
            $attr .= " width=\"{$width}\" height=\"{$height}\"";
        }

        return $attr;
    }

    public function getItemId(string $url = ''): string {
        return "mappingfusion.fr{$url}";
    }

    public function getLinkTypes(): array {
        return LinkType::cases();
    }

    public function getMk(): MarkdownService {
        return $this->mk;
    }

    public function getPathOfResource(string $filename): bool|string {
        return realpath(dirname(__FILE__) . '/../../public/uploaded/' . $filename);
    }

    public function getPathOfPublicFile(string $filename): bool|string {
        return realpath(dirname(__FILE__) . '/../../public/' . $filename);
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

    public function getSha256(string $path): string {
        return hash_file('sha256', $this->getPathOfResource($path));
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