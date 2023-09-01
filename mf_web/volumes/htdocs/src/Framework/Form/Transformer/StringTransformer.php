<?php

namespace MF\Framework\Form\Transformer;

use MF\Exception\Form\MissingInputException;

class StringTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    /**
     * @return string|null The submitted, non-empty string, or null if the string is empty.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): ?string {
        if (!isset($formRawData[$this->name])) {
            throw new MissingInputException($this->name);
        }
        $submittedString = $formRawData[$this->name];
        return '' !== $submittedString ? $submittedString : null;
    }
}