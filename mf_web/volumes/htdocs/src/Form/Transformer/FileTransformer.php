<?php

namespace MF\Form\Transformer;

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
    public function extractValueFromRequest(ServerRequestInterface $request, IFormElement $input): ?string {
        if (!key_exists($input->getName(), $request->getUploadedFiles())) {
            if (key_exists($input->getName() . self::PREVIOUS_SUFFIX, $request->getParsedBody())) {
                return $request->getParsedBody()[$input->getName() . self::PREVIOUS_SUFFIX];
            }
            throw new MissingInputException();
        }
        $uploadedFile = $request->getUploadedFiles()[$input->getName()];

        if (0 === $uploadedFile->getError()) {
            $filename = new SlugFilename($uploadedFile->getClientFilename());
            $uploadedFile->moveTo(dirname(__FILE__) . "/../../../public/uploaded/" . $filename->__toString());
            return $filename->__toString();
        } elseif (4 === $uploadedFile->getError()) {
            return $request->getParsedBody()[$input->getName() . self::PREVIOUS_SUFFIX] ?? null;
        } else {
            throw new UnexpectedValueException();
        }
    }
}