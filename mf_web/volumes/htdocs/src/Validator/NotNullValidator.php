<?php

namespace MF\Validator;

use MF\Constraint\INotNullableConstraint;

class NotNullValidator implements IValidator
{
    public function __construct(
        private INotNullableConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [new ValidationFailure('Data is not allowed to be null.')];
        }
        return [];
    }
}