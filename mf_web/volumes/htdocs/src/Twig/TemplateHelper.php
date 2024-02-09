<?php

namespace MF\Twig;

use DateTimeInterface;
use IteratorAggregate;
use LM\WebFramework\Configuration;
use MF\Enum\LinkType;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\File\FileService;
use LM\WebFramework\Session\SessionManager;
use MF\Languages\Translator;
use MF\MarkdownService;
use MF\Router;
use RuntimeException;
use UnexpectedValueException;

class TemplateHelper
{
    public function __construct(
        private Configuration $config,
        private FileService $fileService,
        private MarkdownService $mk,
        private ResourceManager $resourceManager,
        private Router $router,
        private SessionManager $session,
        private Translator $translator,
    ) {
    }

    public function estimateReadingTime(string $text): int {
        return (int) round(str_word_count($text) / 238);
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
        $websiteUrl = $this->config->getHomeUrl();
        $publicUrl = $this->config->getPublicUrl();
        $version = filemtime(dirname(__FILE__) . '/../../public/' . $filename);
        return "$websiteUrl$publicUrl/$filename?version=$version";
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

    public function getImgAttr(string $alt, string $filename, bool $isResource = true, ?int $width = null, ?int $height = null, bool $smallImg = false): ?string {

        if ($isResource && $smallImg) {
            $filename = $this->resourceManager->getSmallFilename($filename);
        }

        $imgPath =  $isResource ? $this->resourceManager->getResourcePath($filename) : $this->resourceManager->getAssetPath($filename);

        $srcValue = $isResource ? $this->getResource($filename) : $this->getAsset($filename);
        $attr = "alt=\"{$alt}\" src=\"{$srcValue}\"";

        if (false !== file_exists($imgPath)) {
            try {
                $dimensions = getimagesize($imgPath);
            }
            catch (RuntimeException $e) {
                $dimensions = false;
            }
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

    /**
     * @return array<int, string>
     */
    public function getLinkTypes(): array {
        return LinkType::cases();
    }

    public function getMk(): MarkdownService {
        return $this->mk;
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
        $hash = hash_file('sha256', $this->resourceManager->getResourcePath($path));
        if (false === $hash) {
            throw new UnexpectedValueException();
        }
        return $hash;
    }

    public function hasLinks(string $linkType, IteratorAggregate $links): bool {
        foreach ($links as $l) {
            if ($l->type == $linkType) {
                return true;
            }
        }
        return false;
    }

    public function shorten(string $string, int $nCharacters, string $suffix): string {
        return mb_substr($string, 0, $nCharacters) . $suffix;
    }

    public function t(string $text): string {
        return $this->translate($text);
    }

    public function translate(string $text): string {
        return $this->translator->translate($text);
    }
}