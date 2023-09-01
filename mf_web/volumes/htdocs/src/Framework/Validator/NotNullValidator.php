<?php

namespace MF\Framework\Validator;

use MF\Framework\Constraints\IConstraint;
use MF\Framework\DataStructures\ConstraintViolation;

class NotNullValidator implements IValidator
{
    public function __construct(
        private IConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [
                new ConstraintViolation($this->constraint, 'Data is not allowed to be null.'),
            ];
        }

        return [];
    }
}