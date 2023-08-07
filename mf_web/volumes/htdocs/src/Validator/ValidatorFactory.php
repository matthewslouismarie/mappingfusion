<?php

namespace MF\Validator;

use MF\Constraint\ArrayConstraint;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IEnumConstraint;
use MF\Constraint\IModel;
use MF\Constraint\INotNullableConstraint;
use MF\Constraint\IDecimalConstraint;
use MF\Constraint\IStringConstraint;
use MF\Exception\Validation\ValidationException;

class ValidatorFactory
{
    public function createValidator(IConstraint $constraint): IValidator {
        if ($constraint instanceof IDecimalConstraint) {
            return new DecimalNumberValidator($constraint);
        }
        if ($constraint instanceof IStringConstraint) {
            return new StringValidator($constraint);
        }
        if ($constraint instanceof IBooleanConstraint) {
            return new BooleanValidator($constraint);
        }
        if ($constraint instanceof INotNullableConstraint) {
            return new NotNullValidator($constraint);
        }
        if ($constraint instanceof IDateTimeConstraint) {
            return new DateTimeValidator($constraint);
        }
        if ($constraint instanceof IModel) {
            return new EntityValidator($constraint);
        }
        if ($constraint instanceof IArrayConstraint) {
            return new ArrayValidator($constraint);
        }
        if ($constraint instanceof IEnumConstraint) {
            return new EnumValidator($constraint);
        }

        throw new ValidationException();
    }
}