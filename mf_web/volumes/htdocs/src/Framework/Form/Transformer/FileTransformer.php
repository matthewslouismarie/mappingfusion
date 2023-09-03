<?php

namespace MF\Framework\Form\Transformer;

use MF\Framework\Form\Exceptions\MissingInputException;
use MF\Model\SlugFilename;
use UnexpectedValueException;

class FileTransformer implements IFormTransformer
{
    const PREVIOUS_SUFFIX = '_previous';

    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @throws MissingInputException If no file was uploaded.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): ?string {
        if (!key_exists($this->name, $formRawData) && !key_exists($this->name, $uploadedFiles)) {
            if (key_exists($this->name . self::PREVIOUS_SUFFIX, $formRawData)) {
                return $formRawData[$this->name . self::PREVIOUS_SUFFIX];
            }
            return null;
        }
        $uploadedFile = $uploadedFiles[$this->name];

        if (0 === $uploadedFile->getError()) {
            $filename = new SlugFilename($uploadedFile->getClientFilename());
            $uploadedFile->moveTo(dirname(__FILE__) . "/../../../../public/uploaded/" . $filename->__toString());
            return $filename->__toString();
        } elseif (4 === $uploadedFile->getError()) {
            return $formRawData[$this->name . self::PREVIOUS_SUFFIX] ?? null;
        } else {
            throw new UnexpectedValueException();
        }
    }
}