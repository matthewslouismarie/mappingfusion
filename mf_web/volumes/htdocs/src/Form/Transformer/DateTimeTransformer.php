<?php

namespace MF\Form\Transformer;

use DateTimeImmutable;
use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;

class DateTimeTransformer implements FormTransformer
{
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): ?DateTimeImmutable {
        if (!isset($formRawData[$input->getName()])) {
            throw new MissingInputException();
        }

        $submittedString = $formRawData[$input->getName()];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}