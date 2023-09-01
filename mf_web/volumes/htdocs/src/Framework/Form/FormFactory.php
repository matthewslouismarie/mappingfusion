<?php

namespace MF\Framework\Form;

use DomainException;
use InvalidArgumentException;
use MF\Framework\Form\Transformer\ArrayTransformer;
use MF\Framework\Form\Transformer\CsrfTransformer;
use MF\Framework\Form\Transformer\IFormTransformer;
use MF\Framework\Form\Transformer\StringTransformer;
use MF\Framework\Model\IModel;

/**
 * Automatically creates a Form object from a model definition.
 */
class FormFactory
{
    const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private CsrfTransformer $csrfTransformer,
    ) {
    }

    public function createTransformer(IModel $model, array $config = [], ?string $name = null): IFormTransformer {
        if (null !== $model->getArrayDefinition()) {
            $formElements = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                $formElements[$key] = $this->createTransformer($property, $config[$key] ?? [], $key);
            }
            return new ArrayTransformer($formElements, $this->csrfTransformer, $name);

        }
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        if (null !== $model->getStringConstraints()) {
            return new StringTransformer($name);
        }
        
        // return $this->fileTransformer;
        // } elseif ($type instanceof IStringConstraint || $type instanceof IEnumConstraint) {
        // } elseif ($type instanceof IBooleanConstraint) {
        //     return $this->checkboxTransformer;
        // } elseif ($type instanceof IDateTimeConstraint) {
        //     return $this->dateTimeTransformer;
        // } elseif ($type instanceof IArrayConstraint) {
        // } elseif ($type instanceof IDecimalConstraint) {
        //     return $this->stringTransformer;
        // }
        throw new DomainException('No transformer found for ' . get_class($model) . '.');
    }
}