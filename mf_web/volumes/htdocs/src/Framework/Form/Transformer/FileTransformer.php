<?php

namespace MF\Framework\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;
use MF\Model\SlugFilename;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

class FileTransformer implements FormTransformer
{
    const PREVIOUS_SUFFIX = '_previous';

    /**
     * @throws MissingInputException If no file was uploaded.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, string $inputName): ?string {
        if (!key_exists($inputName, $formRawData) && !key_exists($inputName, $uploadedFiles)) {
            if (key_exists($inputName . self::PREVIOUS_SUFFIX, $formRawData)) {
                return $formRawData[$inputName . self::PREVIOUS_SUFFIX];
            }
            return null;
        }
        $uploadedFile = $uploadedFiles[$inputName];

        if (0 === $uploadedFile->getError()) {
            $filename = new SlugFilename($uploadedFile->getClientFilename());
            $uploadedFile->moveTo(dirname(__FILE__) . "/../../../public/uploaded/" . $filename->__toString());
            return $filename->__toString();
        } elseif (4 === $uploadedFile->getError()) {
            return $formRawData[$inputName . self::PREVIOUS_SUFFIX] ?? null;
        } else {
            throw new UnexpectedValueException();
        }
    }
}