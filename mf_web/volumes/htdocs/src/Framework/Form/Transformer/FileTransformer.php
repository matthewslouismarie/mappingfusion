<?php

namespace MF\Framework\Form\Transformer;

use MF\Framework\Form\Exceptions\IllegalUserInputException;
use MF\Framework\Form\Exceptions\MissingInputException;
use Psr\Http\Message\UploadedFileInterface;
use UnexpectedValueException;

class FileTransformer implements IFormTransformer
{
    const PREVIOUS_SUFFIX = '_previous';

    private string $name;

    private string $destinationFolder;
    public function __construct(
        string $name,
    ) {
        $this->name = $name;
        $this->destinationFolder = dirname(__FILE__) . "/../../../../public/uploaded/";
    }

    /**
     * @throws MissingInputException If no file was uploaded.
     * @todo Do not save the image here, just extract the a UploadedFileInterface or an array of it.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): null|array|string {
        if (!key_exists($this->name, $uploadedFiles)) {
            return $this->extractPreviousFilename($formRawData);
        }

        $uploaded = $uploadedFiles[$this->name];

        if (is_array($uploaded)) {
            $filenames = [];
            foreach ($uploaded as $img) {
                $filenames[] = $this->saveUploadedImage($img) ?? $this->extractPreviousFilename($formRawData);
            }
            return $filenames;
        } else {
            return $this->saveUploadedImage($uploaded) ?? $this->extractPreviousFilename($formRawData);
        }
    }

    private function extractPreviousFilename(array $formRawData): null|string {
        if (key_exists($this->name . self::PREVIOUS_SUFFIX, $formRawData)) {
            $oldFilename = pathinfo($formRawData[$this->name . self::PREVIOUS_SUFFIX]);
            if ('.' !== $oldFilename['dirname']) {
                throw new IllegalUserInputException();
            }
            return $oldFilename['basename'];
        } else {
            return null;
        }
    }

    private function saveUploadedImage(UploadedFileInterface $file): null|string {
        if (0 === $file->getError()) {
            $uploadedFileName = pathinfo($file->getClientFilename(), PATHINFO_FILENAME);
            $destinationPath = "{$this->destinationFolder}/{$uploadedFileName}.webp";
            $smallImgDestinationPath = "{$this->destinationFolder}/{$uploadedFileName}.small.webp";
            if (!file_exists($destinationPath)) {
                if('image/webp' !== $file->getClientMediaType()) {
                    $gdImage = imagecreatefromstring($file->getStream());
                    imagewebp($gdImage, $destinationPath, 95);
                } else {
                    $file->moveTo($destinationPath);
                }

                list($width, $height) = getimagesize($destinationPath);

                $scale = max(316 / $width, 208 / $height);

                list($newWidth, $newHeight) = [round($width * $scale), round($height * $scale)];


                $smallImg = imagecreatetruecolor($newWidth, $newHeight);

                imagecopyresized($smallImg, $gdImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagewebp($smallImg, $smallImgDestinationPath, 75);
            }
            return pathinfo($destinationPath)['basename'];
        } elseif (4 === $file->getError()) {
            return null;
        } else {
            throw new UnexpectedValueException();
        }
    }
}