<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;

class StringTransformer implements FormTransformer
{
    /**
     * @return string The submitted, non-empty string.
     * @return null If an empty string was submitted.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): ?string {
        if (!isset($formRawData[$input->getName()])) {
            throw new MissingInputException($input);
        }
        $submittedString = $formRawData[$input->getName()];
        return '' !== $submittedString ? $submittedString : null;
    }
}