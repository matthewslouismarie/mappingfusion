<?php

namespace MF\Framework\Validator;

use MF\Framework\Constraints\RangeConstraint;
use MF\Framework\DataStructures\ConstraintViolation;

class RangeValidator implements IValidator
{
    public function __construct(
        private RangeConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        $violations = [];
        if (null !== $this->constraint->getMax() && $data > $this->constraint->getMax()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$data is higher than set maximum.");
        }
        if (null !== $this->constraint->getMin() && $data < $this->constraint->getMin()) {
            $violations[] =  new ConstraintViolation($this->constraint, "$data is lower than set minimum.");
        }
        return $violations;
    }
}