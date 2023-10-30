<?php

namespace MF\Twig;

use DateTimeInterface;
use IteratorAggregate;
use MF\Configuration;
use MF\Enum\LinkType;
use MF\Framework\DataStructures\AppObject;
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
        private ResourceManager $resourceManager,
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

    public function getThumbnail(AppObject $article): string {
        $original = $article->thumbnail_filename ?? $article->cover_filename;
    
        return $this->resourceManager->getSmallFilename($original);
    }

    public function getThumbnailUrl(AppObject $article): string {
        return $this->getResource($this->getThumbnail($article));
    }

    public function getAsset(string $filename): string {
        $publicUrl = $this->config->getPublicUrl();
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "$publicUrl/$filename?version=$version";
    }

    public function getConf(): Configuration {
        return $this->config;
    }

    public function getDate(DateTimeInterface $date): string {
        return $date->format('Y-M-D');
    }

    public function getImages(string $text): array {
        $foundImages = [];

        preg_match_all('/(?<=<!---img )(.+)(?= -->)/', $text, $foundImages);

        return $foundImages[0];
    }

    public function getImgAttr(string $alt, string $filename, bool $isResource = true, ?int $width = null, ?int $height = null, bool $smallImg = false): string {

        if ($isResource && $smallImg) {
            $filename = $this->resourceManager->getSmallFilename($filename);
        }

        $srcValue = $isResource ? $this->getResource($filename) : $this->getAsset($filename);
        $attr = "alt=\"{$alt}\" src=\"{$srcValue}\"";

        $filePathOnDisk =  $isResource ? $this->resourceManager->getResourcePath($filename) : $this->getPathOfPublicFile($filename);
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

    public function getPathOfPublicFile(string $filename): bool|string {
        return realpath(dirname(__FILE__) . '/../../public/' . $filename);
    }

    public function getResource(string $filename): string {
        return $this->resourceManager->getResourceUrl($filename);
    }

    public function getRm(): ResourceManager {
        return $this->resourceManager;
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getSession(): SessionManager {
        return $this->session;
    }

    public function getSha256(string $path): string {
        return hash_file('sha256', $this->resourceManager->getResourcePath($path));
    }

    public function hasLinks(string $linkType, IteratorAggregate $links): bool {
        foreach ($links as $l) {
            if ($l->type == $linkType) {
                return true;
            }
        }
        return false;
    }

    public function shorten(string $string, int $nCharacters, string $suffix) {
        return mb_substr($string, 0, $nCharacters) . $suffix;
    }
}