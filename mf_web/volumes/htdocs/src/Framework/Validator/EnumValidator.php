<?php

namespace MF\Framework\Validator;

use MF\Framework\Constraints\EnumConstraint;
use MF\Framework\DataStructures\ConstraintViolation;

class EnumValidator implements IValidator
{
    public function __construct(
        private EnumConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (!in_array($data, $this->constraint->getValues(), true)) {
            return [
                new ConstraintViolation($this->constraint, 'Data does not adhere to the list of permitted values.'),
            ];
        }
        return [];
    }
}