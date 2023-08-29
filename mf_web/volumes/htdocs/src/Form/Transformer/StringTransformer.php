<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;

class StringTransformer implements FormTransformer
{
    /**
     * @return string|null The submitted, non-empty string, or null if the string is empty.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, string $inputName): ?string {
        if (!isset($formRawData[$inputName])) {
            throw new MissingInputException($inputName);
        }
        $submittedString = $formRawData[$inputName];
        return '' !== $submittedString ? $submittedString : null;
    }
}