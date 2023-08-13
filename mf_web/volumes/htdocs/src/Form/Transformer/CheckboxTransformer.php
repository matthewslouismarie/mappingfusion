<?php

namespace MF\Form\Transformer;

use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

class CheckboxTransformer implements FormTransformer
{
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): bool {
        if (!isset($formRawData[$input->getName()])) {
            return false;
        }
        return 'on' === $formRawData[$input->getName()] ? true : false;
    }
}