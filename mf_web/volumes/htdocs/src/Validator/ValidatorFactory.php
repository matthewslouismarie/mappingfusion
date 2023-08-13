<?php

namespace MF\Validator;

use InvalidArgumentException;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IEnumConstraint;
use MF\Constraint\IFileConstraint;
use MF\Constraint\IModel;
use MF\Constraint\INotNullableConstraint;
use MF\Constraint\IDecimalConstraint;
use MF\Constraint\IStringConstraint;

class ValidatorFactory
{
    /**
     * @throws InvalidArgumentException If no validator could be found for the constraint. 
     */
    public function createValidator(IConstraint $constraint): IValidator {
        if ($constraint instanceof IDecimalConstraint) {
            return new DecimalNumberValidator($constraint);
        }
        if ($constraint instanceof IFileConstraint) {
            return new MemberUploadedImageValidator($constraint);
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

        throw new InvalidArgumentException('No validator could be found for constraint of class ' . get_class($constraint) . '.');
    }
}