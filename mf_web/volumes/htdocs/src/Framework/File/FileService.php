<?php

namespace MF\Framework\File;

class FileService
{
    /**
     * @todo Assume that filenames are one-byte encoded.
     * @todo Assume that filenames are in lowercase.
     * @todo Hard-coded file extensions.
     */
    public function getUploadedImages(): array {
        $listOfFiles = scandir(dirname(__FILE__) . '/../../../public/uploaded/');
        return array_filter($listOfFiles, function ($value) {
            $parts = explode('.', $value);
            $nParts = count($parts);
            return $nParts > 1 && in_array($parts[$nParts - 1], ['jpg', 'jpeg', 'png'], true);
        });
    }
}