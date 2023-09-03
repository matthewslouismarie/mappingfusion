<?php

namespace MF\Framework\Form\Transformer;

use MF\Exception\Form\ExtractionException;
use MF\Framework\Form\FormFactory;
use MF\Framework\Model\IModel;

class ListTransformer implements IFormTransformer
{
    public function __construct(
        private IModel $nodeModel,
        private array $nodeConfig,
        private FormFactory $formFactory,
        private string $name,
    ) {
    }

    public function extractValueFromRequest(array $requestParsedBody, array $uploadedFiles): array {
        $data = $requestParsedBody[$this->name] ?? null;
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            throw new ExtractionException('Une erreur s’est produite.');
        }
        $value = [];
        foreach ($data as $name => $element) {
            if (null !== $this->nodeModel->getArrayDefinition()) {
                $value[] = $this->formFactory
                    ->createTransformer($this->nodeModel, $this->nodeConfig, csrf: false)
                    ->extractValueFromRequest($element, $uploadedFiles)
                ;
            } else {
                $value[] = $this->formFactory
                    ->createTransformer($this->nodeModel, $this->nodeConfig, name: $name)
                    ->extractValueFromRequest($element, $uploadedFiles)
                ;
            }
            
        }

        return $value;
    }
}