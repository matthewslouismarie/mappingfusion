<?php

namespace MF\Form;

use MF\Enum\ModelPropertyType;
use MF\Form\StdFormElement;
use MF\Form\Transformer\CheckboxTransformer;
use MF\Form\Transformer\DateTimeTransformer;
use MF\Form\Transformer\FileTransformer;
use MF\Form\Transformer\FormTransformer;
use MF\Form\Transformer\StringTransformer;
use MF\Model\ArticleDefinition;
use MF\Model\ModelProperty;
use UnexpectedValueException;

class FormManager
{
    public function __construct(
        private CheckboxTransformer $checkboxTransformer,
        private DateTimeTransformer $dateTimeTransformer,
        private FileTransformer $fileTransformer,
        private StringTransformer $stringTransformer,
    ) {
    }

    public function createFormDefinition(
        ArticleDefinition $definition,
        string $prefix = '',
        array $defaultData = null,
        array $formConfig = [],
    ): FormDefinition {
        $htmlFormElements = [];

        foreach ($definition->getProperties() as $property) {
            if (!$property->isAutoGenerated()) {
                $formElementConfig = $formConfig[$property->getName()] ?? null;
                $transformer = $this->getTransformer($property);
                $htmlFormElements[] = new StdFormElement(
                    $prefix . $property->getName(),
                    $transformer,
                    defaultValue: $defaultData[$prefix . $property->getName()] ?? null,
                    isRequired: $formElementConfig['required'] ?? $property->isRequired(),
                    validators: [],
                );
            }
        }
        return new FormDefinition($htmlFormElements, $defaultData);
    }

    private function getTransformer(ModelProperty $property): FormTransformer {
        switch ($property->getType()) {
            case ModelPropertyType::BOOL:
                return $this->checkboxTransformer;

            case ModelPropertyType::DATETIME:
                return $this->dateTimeTransformer;

            case ModelPropertyType::IMAGE:
                return $this->fileTransformer;

            case ModelPropertyType::TEXT:
                return $this->stringTransformer;

            case ModelPropertyType::VARCHAR:
                return $this->stringTransformer;

            default:
                throw new UnexpectedValueException();
        }
    }
}