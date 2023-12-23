<?php

namespace MF\Twig;

use InvalidArgumentException;
use MF\Configuration;
use MF\Framework\DataStructures\Filename;

class ResourceManager
{
    private string $uploadedFolderPath;

    public function __construct(
        private Configuration $config,
    ) {
        $this->uploadedFolderPath = realpath(dirname(__FILE__) . '/../../public/uploaded');
    }

    public function exists(string $filename): bool {
        return file_exists($this->getResourcePath($filename));
    }

    /**
     * @return array<int, int>
     */
    public function getResourceDimensions(string $filename): ?array {
        $filePathOnDisk =  $this->getResourcePath($filename);
        if (false !== $filePathOnDisk) {
            $dimensions = getimagesize($filePathOnDisk);
            if (false !== $dimensions) {
                return $dimensions;
            }
        }
        return null;
    }

    /**
     * @return string The predicted path of the resource specified by the
     * filename, even if the resource does not exist.
     */
    public function getResourcePath(string $filename): string {
        $path = $this->uploadedFolderPath . '/' . $filename;
        if (false === $path) {
            throw new InvalidArgumentException();
        }
        return $path;
    }

    public function getResourceUrl(string $filename): string {
        $homeUrl = $this->config->getHomeUrl();
        $publicUrl = $this->config->getPublicUrl();
        return "$homeUrl$publicUrl/uploaded/$filename";
    }
    
    public function getSmallFilename(string $filename): string {
        $filenameObject = new Filename($filename);
        $fileExtension = $filenameObject->getExtension();
        $filenameNoExtension = $filenameObject->getFilenameNoExtension();
        return "$filenameNoExtension.small.$fileExtension";
    }
    
    public function getMediumFilename(string $filename): string {
        $filenameObject = new Filename($filename);
        $fileExtension = $filenameObject->getExtension();
        $filenameNoExtension = $filenameObject->getFilenameNoExtension();
        return "$filenameNoExtension.medium.$fileExtension";
    }
}