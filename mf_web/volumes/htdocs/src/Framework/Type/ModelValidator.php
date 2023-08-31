<?php

namespace MF\Framework\Type;

use DomainException;
use InvalidArgumentException;
use MF\Framework\Model\IEntity;
use MF\Framework\Model\IModel;
use MF\Framework\Model\StringModel;
use MF\Framework\Validator\ValidatorFactory;

class ModelValidator
{
    public function __construct(
        private ValidatorFactory $validatorFactory,
    ) {
    }

    /**
     * @throws InvalidArgumentException If $data is not of the expected class or type.
     * @throws DomainException If $model is unknown.
     */
    public function validate(mixed $data, IModel $model): array {
        $constraintViolations = [];

        if (null === $data && !$model->isNullable()) {
            throw new InvalidArgumentException('Null is not allowed by model.');
        }
        if (ModelType::Entity === $model->getType()) {
            if (!is_array($data)) {
                throw new InvalidArgumentException('Variable must be an array to be validated against an entity model.');
            }
        } elseif (ModelType::String === $model->getType()) {
            if (!is_string($data)) {
                throw new InvalidArgumentException('Variable must be a string to be validated against the string model.');
            }
        }
        foreach ($model->getConstraints() as $constraint) {
            $violations = $this->validatorFactory->createValidator($constraint, $this)->validate($data);
            if (count($violations) > 0) {
                $constraintViolations = array_merge_recursive($constraintViolations, $violations);
            }
        }
        return $constraintViolations;
        // throw new DomainException('Type ' . get_class($model) . ' is unknown.');
    }
}