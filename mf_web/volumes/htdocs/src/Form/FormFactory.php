<?php

namespace MF\Form;

use InvalidArgumentException;
use MF\Constraint\ArrayConstraint;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IDecimalConstraint;
use MF\Constraint\IEnumConstraint;
use MF\Constraint\IFileConstraint;
use MF\Constraint\IModel;
use MF\Constraint\IStringConstraint;
use MF\Constraint\IType;
use MF\Form\StdFormElement;
use MF\Form\Transformer\ArrayTransformer;
use MF\Form\Transformer\CheckboxTransformer;
use MF\Form\Transformer\CsrfTransformer;
use MF\Form\Transformer\DateTimeTransformer;
use MF\Form\Transformer\FileTransformer;
use MF\Form\Transformer\FormTransformer;
use MF\Form\Transformer\StringTransformer;
use MF\Model\ModelProperty;
use MF\Session\SessionManager;
use MF\Validator\ValidatorFactory;
use RuntimeException;

/**
 * Automatically creates a Form object from a model definition.
 */
class FormFactory
{
    const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private CheckboxTransformer $checkboxTransformer,
        private CsrfTransformer $csrfTransformer,
        private DateTimeTransformer $dateTimeTransformer,
        private FileTransformer $fileTransformer,
        private SessionManager $session,
        private StringTransformer $stringTransformer,
        private ValidatorFactory $validatorFactory,
    ) {
    }

    public function createForm(
        IModel $model,
        string $prefix = '',
        array $formConfig = [],
        bool $csrf = true,
    ): Form {
        $htmlFormElements = [];

        foreach ($model->getProperties() as $property) {
            $formElementConfig = $formConfig[$property->getName()] ?? [];
            if (!($formElementConfig['generated'] ?? $property->isGenerated())) {

                $transformer = $this->getTransformer($property->getType(), $formElementConfig);

                $validators = $this->getValidators($property);

                $htmlFormElements[] = new StdFormElement(
                    $prefix . $property->getName(),
                    $transformer,
                    isRequired: $formElementConfig['required'] ?? $property->isRequired(),
                    validators: $validators,
                );
            }
        }
        if ($csrf) {
            $htmlFormElements[] = $this->getCsrfFormElement();
        }
        return new Form($htmlFormElements, ignoreValueOf: self::CSRF_FORM_ELEMENT_NAME);
    }

    public function getCsrfFormElement(): IFormElement {
        return new StdFormElement(
            self::CSRF_FORM_ELEMENT_NAME,
            $this->csrfTransformer,
        );
    }

    private function getTransformer(IType $type, array $formConfig): FormTransformer {
        if ($type instanceof IFileConstraint) {
            return $this->fileTransformer;
        } elseif ($type instanceof IStringConstraint || $type instanceof IEnumConstraint) {
            return $this->stringTransformer;
        } elseif ($type instanceof IBooleanConstraint) {
            return $this->checkboxTransformer;
        } elseif ($type instanceof IDateTimeConstraint) {
            return $this->dateTimeTransformer;
        } elseif ($type instanceof IArrayConstraint) {
            return new ArrayTransformer($this->createForm($type->getElementType(), formConfig: $formConfig, csrf: false));
        } elseif ($type instanceof IDecimalConstraint) {
            return $this->stringTransformer;
        }
        throw new InvalidArgumentException('No transformer found for ' . get_class($type) . '.');
    }

    /**
     * @return \MF\Validator\IValidator[]
     */
    private function getValidators(ModelProperty $property): array {
        $validators = [$this->validatorFactory->createValidator($property->getType())];
        foreach ($property->getConstraints() as $c) {
            $validators[] = $this->validatorFactory->createValidator($c);
        }
        return $validators;
    }
}