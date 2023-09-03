<?php

namespace MF\Framework\Validator;

use DomainException;
use MF\Framework\Constraints\EnumConstraint;
use MF\Framework\Constraints\IConstraint;
use MF\Framework\Constraints\EntityConstraint;
use MF\Framework\Constraints\INotNullConstraint;
use MF\Framework\Constraints\INumberConstraint;
use MF\Framework\Constraints\IUploadedImageConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Type\ModelValidator;
use MF\Validator\MemberUploadedImageValidator;

class ValidatorFactory
{
    /**
     * @throws DomainException If no validator is associated with the constraint.
     */
    public function createValidator(IConstraint $constraint, ModelValidator $modelValidator) {
        if ($constraint instanceof INotNullConstraint) {
            return new NotNullValidator($constraint);
        }
        if ($constraint instanceof EntityConstraint) {
            return new EntityValidator($constraint, $modelValidator);
        }
        if ($constraint instanceof StringConstraint) {
            return new StringValidator($constraint);
        }
        if ($constraint instanceof EnumConstraint) {
            return new EnumValidator($constraint);
        }
        if ($constraint instanceof INumberConstraint) {
            return new RangeValidator($constraint);
        }
        if ($constraint instanceof IUploadedImageConstraint) {
            return new UploadedImageValidator($constraint);
        }
        throw new DomainException('Constraint of type ' . get_class($constraint) . ' is unknown.');
    }
}