<?php

namespace MF\Form\Transformer;

use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

class CheckboxTransformer implements FormTransformer
{
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, string $inputName): bool {
        if (!isset($formRawData[$inputName])) {
            return false;
        }
        return 'on' === $formRawData[$inputName] ? true : false;
    }
}