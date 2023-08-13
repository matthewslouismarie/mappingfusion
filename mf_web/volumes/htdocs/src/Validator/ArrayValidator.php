<?php

namespace MF\Validator;

use MF\Constraint\IArrayConstraint;

class ArrayValidator implements IValidator
{
    public function __construct(
        private IArrayConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            return [new ValidationFailure('Array data is not an array.')];
        }

        return [];
    }
}