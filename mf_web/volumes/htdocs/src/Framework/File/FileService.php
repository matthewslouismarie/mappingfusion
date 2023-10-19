<?php

namespace MF\Framework\File;
use MF\Framework\Constraints\IUploadedImageConstraint;

class FileService
{
    /**
     * @todo Assume that filenames are one-byte encoded.
     * @todo Assume that filenames are in lowercase.
     * @todo Hard-coded file extensions.
     */
    public function getUploadedImages(): array {
        $listOfFiles = scandir(dirname(__FILE__) . '/../../../public/uploaded/');

        return array_filter(
            $listOfFiles,
            fn ($value) => 1 === preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $value),
        );
    }
}