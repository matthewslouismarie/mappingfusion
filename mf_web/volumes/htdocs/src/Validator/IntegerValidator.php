<?php

namespace MF\Validator;

use MF\Constraint\INumberConstraint;

class IntegerValidator implements IValidator
{
    public function __construct(
        private INumberConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!is_int($data)) {
            return [new ValidationFailure("Data is not an integer.")];
        }
        $max = $this->constraint->getMax();
        if (null !== $max) {
            if ($data > $max) {
                return [new ValidationFailure("$data cannot be higher than $max.")];
            }
        }
        $min = $this->constraint->getMin();
        if (null !== $min) {
            if ($data < $min) {
                return [new ValidationFailure("$data cannot be lower than $min.")];
            }
        }
        return [];
    }
}