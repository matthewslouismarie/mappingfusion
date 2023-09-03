<?php

namespace MF\Framework\Form\Transformer;

use DateTimeImmutable;
use MF\Exception\Form\MissingInputException;

class DateTimeTransformer implements IFormTransformer
{
    public function __construct(
        private string $name,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): ?DateTimeImmutable {
        if (!key_exists($this->name, $formRawData)) {
            throw new MissingInputException();
        }

        $submittedString = $formRawData[$this->name];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}