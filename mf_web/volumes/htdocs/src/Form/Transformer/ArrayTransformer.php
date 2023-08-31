<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\ExtractionException;
use MF\Form\IFormExtractor;

class ArrayTransformer implements FormTransformer
{
    public function __construct(
        private IFormExtractor $formDataFactory,
    ) {
    }

    public function extractValueFromRequest(array $requestParsedBody, array $uploadedFiles, string $inputName): array {
        if (!key_exists($inputName, $requestParsedBody)) {
            return [];
        }
        if (!is_array($requestParsedBody[$inputName])) {
            throw new ExtractionException('Une erreur s’est produite.');
        }

        $appArray = [];
        foreach ($requestParsedBody[$inputName] as $subRequestParsedBody) {
            $appArray[] = $this->formDataFactory->extractFromRequest($subRequestParsedBody, $uploadedFiles);
        }
    
        return $appArray;
    }
}