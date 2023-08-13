<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\ExtractionException;
use MF\Form\Form;
use MF\Form\IFormElement;

class ArrayTransformer implements FormTransformer
{
    public function __construct(
        private Form $submittable,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): array {
        if (!key_exists($input->getName(), $formRawData)) {
            return [];
        }
        if (!is_array($formRawData[$input->getName()])) {
            throw new ExtractionException('Une erreur s’est produite.');
        }

        $appArray = [];
        foreach ($formRawData[$input->getName()] as $subFormRawData) {
            $appArray[] = $this->submittable->extractFormData($subFormRawData, $uploadedFiles);
        }
        return $appArray;
    }
}