<?php

namespace MF\Validator;

use MF\Constraint\IEnumConstraint;

class EnumValidator implements IValidator
{
    public function __construct(
        private IEnumConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!in_array($data, $this->constraint->getAcceptedValues())) {
            return [new ValidationFailure("Given data does not belong to the accepted values.")];
        }
        return [];
    }
}