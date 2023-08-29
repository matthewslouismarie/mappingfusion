<?php

namespace MF\Form\Transformer;

use DateTimeImmutable;
use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;

class DateTimeTransformer implements FormTransformer
{
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, string $inputName): ?DateTimeImmutable {
        if (!isset($formRawData[$inputName])) {
            throw new MissingInputException();
        }

        $submittedString = $formRawData[$inputName];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}