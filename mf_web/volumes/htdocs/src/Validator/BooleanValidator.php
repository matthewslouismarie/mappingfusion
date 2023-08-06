<?php

namespace MF\Validator;

use MF\Constraint\IBooleanConstraint;

class BooleanValidator implements IValidator
{
    public function __construct(IBooleanConstraint $constraint) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!is_bool($data)) {
            return [new ValidationFailure("Given data is not a boolean.")];
        }
        return [];
    }
}