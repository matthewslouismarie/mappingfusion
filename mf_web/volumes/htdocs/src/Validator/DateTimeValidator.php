<?php

namespace MF\Validator;

use DateTimeImmutable;
use MF\Constraint\IDateTimeConstraint;

class DateTimeValidator implements IValidator
{
    public function __construct(IDateTimeConstraint $constraint) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!($data instanceof DateTimeImmutable)) {
            return [new ValidationFailure("Given data is not a DateTimeImmutable.")];
        }
        return [];
    }
}