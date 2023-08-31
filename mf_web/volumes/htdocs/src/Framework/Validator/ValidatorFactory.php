<?php

namespace MF\Framework\Validator;

use DomainException;
use MF\Framework\Constraints\IConstraint;
use MF\Framework\Constraints\EntityConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Type\ModelValidator;

class ValidatorFactory
{
    /**
     * @throws DomainException If no validator is associated with the constraint.
     */
    public function createValidator(IConstraint $constraint, ModelValidator $modelValidator) {
        if ($constraint instanceof EntityConstraint) {
            return new EntityValidator($constraint, $modelValidator);
        }
        if ($constraint instanceof StringConstraint) {
            return new StringValidator($constraint);
        }
        throw new DomainException('Constraint of type ' . get_class($constraint) . ' is unknown.');
    }
}