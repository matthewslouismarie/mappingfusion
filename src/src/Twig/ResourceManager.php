<?php

namespace MF\Twig;

use LM\WebFramework\Configuration;
use LM\WebFramework\DataStructures\Filename;

class ResourceManager
{
    private string $assetFolderPath;

    private string $resourceFolderPath;

    public function __construct(
        private Configuration $config,
    ) {
        $this->assetFolderPath = realpath(dirname(__FILE__) . '/../../public');
        $this->resourceFolderPath = realpath(dirname(__FILE__) . '/../../public/uploaded');
    }

    public function exists(string $filename): bool {
        return file_exists($this->getResourcePath($filename));
    }

    public function getAssetPath(string $filename): string {
        return $this->assetFolderPath . '/' . $filename;
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
        return $this->resourceFolderPath . '/' . $filename;
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