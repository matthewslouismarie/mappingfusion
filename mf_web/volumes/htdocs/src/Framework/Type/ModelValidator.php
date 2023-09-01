<?php

namespace MF\Framework\Type;

use DomainException;
use InvalidArgumentException;
use MF\Constraint\INotNullableConstraint;
use MF\Framework\Constraints\INotNullConstraint;
use MF\Framework\DataStructures\ConstraintViolation;
use MF\Framework\Model\IEntity;
use MF\Framework\Model\IModel;
use MF\Framework\Model\IString;
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

        if (!is_scalar($data) && !is_array($data) && null !== $data) {
        }
        
        if (null === $data) {
            if (!$model->isNullable()) {
                return [
                    new ConstraintViolation(
                        new class implements INotNullConstraint {},
                        'Data is not allowed to be null.',
                    ),
                ];
            }
        } elseif (is_array($data)) {
            $arrayDefinition = $model->getArrayDefinition();
            if (null === $arrayDefinition) {
                throw new InvalidArgumentException('The provided model does not provide a definition for arrays.');
            }
            if (count($arrayDefinition) !== count($data)) {
                throw new InvalidArgumentException('The provided array does not have the expected number of properties.');
            }
            foreach ($arrayDefinition as $key => $property) {
                $violations = $this->validate($data[$key], $property);
                if (count($violations) > 0) {
                    $constraintViolations[$key] = $violations;
                }
            }
        } elseif (is_string($data)) {
            $stringConstraints = $model->getStringConstraints();
            if (null === $stringConstraints) {
                throw new InvalidArgumentException('The provided model does not provide a definition for arrays.');
            }
            foreach ($stringConstraints as $c) {
                $violations = $this->validatorFactory->createValidator($c, $this)->validate($data);
                if (count($violations) > 0) {
                    $constraintViolations = array_merge_recursive($violations);
                }
            }
        } else {
            throw new InvalidArgumentException("Data to be validated must be scalar.");
        }

        return $constraintViolations;
    }
}