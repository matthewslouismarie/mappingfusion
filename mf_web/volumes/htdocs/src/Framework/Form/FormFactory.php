<?php

namespace MF\Framework\Form;

use DomainException;
use InvalidArgumentException;
use MF\Framework\Constraints\IUploadedImageConstraint;
use MF\Framework\Form\Transformer\ArrayTransformer;
use MF\Framework\Form\Transformer\CheckboxTransformer;
use MF\Framework\Form\Transformer\CsrfTransformer;
use MF\Framework\Form\Transformer\DateTimeTransformer;
use MF\Framework\Form\Transformer\FileTransformer;
use MF\Framework\Form\Transformer\IFormTransformer;
use MF\Framework\Form\Transformer\ListTransformer;
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

    public function createForm(IModel $model, array $config = []): ArrayTransformer {
        if (null === $model->getArrayDefinition()) {
            throw new InvalidArgumentException('Model must possess an array definition.');
        }
        return $this->createTransformer($model, $config, null, true);
    }

    public function createTransformer(IModel $model, array $config = [], ?string $name = null, bool $csrf = false): IFormTransformer {
        if (null !== $model->getArrayDefinition()) {
            $formElements = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                if (!isset($config[$key]['ignore']) || false === $config[$key]['ignore']) {
                    $formElements[$key] = $this->createTransformer($property, $config[$key] ?? [], $key);
                }
            }
            return new ArrayTransformer($formElements, $csrf ? $this->csrfTransformer : null, $name);

        }
        if (null === $name) {
            throw new InvalidArgumentException('A name must be provided for non-array transformers.');
        }
        if (null !== $model->getListNodeModel()) {
            return new ListTransformer($model->getListNodeModel(), $config, $this, $name);
        }
        if (null !== $model->getStringConstraints() || null !== $model->getIntegerConstraints()) {
            if (null !== $model->getStringConstraints()) {
                foreach ($model->getStringConstraints() as $c) {
                    if ($c instanceof IUploadedImageConstraint) {
                        return new FileTransformer($name);
                    }
                }
            }
            return new StringTransformer($name);
        }
        if (null !== $model->getDateTimeConstraints()) {
            return new DateTimeTransformer($name);
        }
        if ($model->isBool()) {
            return new CheckboxTransformer($name);
        }

        throw new DomainException('No transformer found for ' . get_class($model) . '.');
    }
}