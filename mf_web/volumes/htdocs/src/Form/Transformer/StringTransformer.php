<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;

class StringTransformer implements FormTransformer
{
    /**
     * @return string|null The submitted, non-empty string, or null if the string is empty.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): ?string {
        if (!isset($formRawData[$input->getName()])) {
            throw new MissingInputException($input);
        }
        $submittedString = $formRawData[$input->getName()];
        return '' !== $submittedString ? $submittedString : null;
    }
}