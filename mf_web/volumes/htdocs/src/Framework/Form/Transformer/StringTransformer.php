<?php

namespace MF\Framework\Form\Transformer;

use MF\Framework\Form\Exceptions\MissingInputException;

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
        if (!key_exists($this->name, $formRawData)) {
            throw new MissingInputException($this->name);
        }
        $submittedString = $formRawData[$this->name];
        return '' !== $submittedString ? $submittedString : null;
    }
}