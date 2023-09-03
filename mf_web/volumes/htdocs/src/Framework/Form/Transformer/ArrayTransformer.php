<?php

namespace MF\Framework\Form\Transformer;

use MF\Framework\Form\Exceptions\ExtractionException;

class ArrayTransformer implements IFormTransformer
{
    /**
     * @param \MF\Framework\Form\IFormElement $formElements
     */
    public function __construct(
        private array $formElements,
        private ?CsrfTransformer $csrf = null,
        private ?string $name = null,
    ) {
    }

    public function extractValueFromRequest(array $requestParsedBody, array $uploadedFiles): array {
        $data = null === $this->name ? $requestParsedBody : $requestParsedBody[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }

        $value = [];
        foreach ($this->formElements as $key => $transformer) {
            $value[$key] = $transformer->extractValueFromRequest($data, $uploadedFiles);
        }
        if (null !== $this->csrf) {
            $this->csrf->extractValueFromRequest($data, $uploadedFiles);
        }
        return $value;
    }
}