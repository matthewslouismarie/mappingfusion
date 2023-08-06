<?php

namespace MF\Form;

use MF\Constraint\IModel;
use MF\Enum\ModelPropertyType;
use MF\Form\StdFormElement;
use MF\Form\Transformer\CheckboxTransformer;
use MF\Form\Transformer\DateTimeTransformer;
use MF\Form\Transformer\FileTransformer;
use MF\Form\Transformer\FormTransformer;
use MF\Form\Transformer\StringTransformer;
use MF\Model\IModelProperty;
use UnexpectedValueException;

/**
 * Automatically creates a Form object from a model definition.
 */
class FormFactory
{
    public function __construct(
        private CheckboxTransformer $checkboxTransformer,
        private DateTimeTransformer $dateTimeTransformer,
        private FileTransformer $fileTransformer,
        private StringTransformer $stringTransformer,
    ) {
    }

    public function createForm(
        IModel $definition,
        string $prefix = '',
        array $defaultData = null,
        array $formConfig = [],
    ): Form {
        $htmlFormElements = [];

        foreach ($definition->getProperties() as $property) {
            if (!$property->isGenerated()) {
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
        return new Form($htmlFormElements, $defaultData);
    }

    private function getTransformer(IModelProperty $property): FormTransformer {
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