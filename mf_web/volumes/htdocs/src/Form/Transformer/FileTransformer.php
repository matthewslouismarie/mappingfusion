<?php

namespace MF\Form\Transformer;

use MF\Exception\InvalidFormException\MissingInputException;
use MF\Form\FormElement;
use MF\Model\SlugFilename;
use Psr\Http\Message\ServerRequestInterface;
use UnexpectedValueException;

class FileTransformer implements FormTransformer
{
    /**
     * @throws MissingInputException If no file was uploaded.
     */
    public function extractValueFromRequest(ServerRequestInterface $request, FormElement $input): ?string {
        if (!isset($request->getUploadedFiles()[$input->getName()])) {
            throw new MissingInputException();
        }
        $uploadedFile = $request->getUploadedFiles()[$input->getName()];

        if (0 === $uploadedFile->getError()) {
            $filename = new SlugFilename($uploadedFile->getClientFilename());
            $uploadedFile->moveTo(dirname(__FILE__) . "/../../../public/uploaded/" . $filename->__toString());
            return $filename->__toString();
        } elseif (4 === $uploadedFile->getError()) {
            return null;
        } else {
            throw new UnexpectedValueException();
        }
    }
}