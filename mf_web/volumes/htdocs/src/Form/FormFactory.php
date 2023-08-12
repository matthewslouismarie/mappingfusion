<?php

namespace MF\Form;

use MF\Constraint\IModel;
use MF\Constraint\IStringConstraint;
use MF\Constraint\IType;
use MF\Form\StdFormElement;
use MF\Form\Transformer\CheckboxTransformer;
use MF\Form\Transformer\CsrfTransformer;
use MF\Form\Transformer\DateTimeTransformer;
use MF\Form\Transformer\FileTransformer;
use MF\Form\Transformer\FormTransformer;
use MF\Form\Transformer\StringTransformer;
use MF\Session\SessionManager;
use RuntimeException;

/**
 * Automatically creates a Form object from a model definition.
 */
class FormFactory
{
    const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private CheckboxTransformer $checkboxTransformer,
        private DateTimeTransformer $dateTimeTransformer,
        private FileTransformer $fileTransformer,
        private StringTransformer $stringTransformer,
        private SessionManager $session,
        private CsrfTransformer $csrfTransformer,
    ) {
    }

    public function createForm(
        IModel $model,
        string $prefix = '',
        array $defaultData = null,
        array $formConfig = [],
    ): Form {
        $htmlFormElements = [];

        foreach ($model->getProperties() as $property) {
            if (!$property->isGenerated()) {
                $formElementConfig = $formConfig[$property->getName()] ?? null;

                $transformer = $this->getTransformer($property->getType());

                $htmlFormElements[] = new StdFormElement(
                    $prefix . $property->getName(),
                    $transformer,
                    defaultValue: $defaultData[$prefix . $property->getName()] ?? null,
                    isRequired: $formElementConfig['required'] ?? $property->isRequired(),
                    validators: [],
                );
            }
        }
        $htmlFormElements[] = $this->getCsrfFormElement();
        return new Form($htmlFormElements, $defaultData);
    }

    public function getCsrfFormElement(): IFormElement {
        return new StdFormElement(
            self::CSRF_FORM_ELEMENT_NAME,
            $this->csrfTransformer,
            $this->session->getCsrf(),
        );
    }

    private function getTransformer(IType $type): FormTransformer {
        if ($type instanceof IStringConstraint) {
            return $this->stringTransformer;
        }
        throw new RuntimeException();
    }
}