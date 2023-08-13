<?php

namespace MF\Validator;

use MF\Constraint\IFileConstraint;

class MemberUploadedImageValidator implements IValidator
{
    public function __construct(
        private IFileConstraint $constraint,
    ) {
    }

    /**
     * @todo Create a validator using only strlen.
     */
    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!is_string($data)) {
            return [new ValidationFailure('Data is not a string.' . var_export($data, true))];
        }
        $maxLength = $this->constraint->getMaxLength();
        if (null !== $maxLength) {
            if (mb_strlen($data) > $maxLength) {
                return [new ValidationFailure("Data cannot be longer than $maxLength caracters.")];
            }
        }
        $minLength = $this->constraint->getMinLength();
        if (null !== $minLength) {
            if (mb_strlen($data) < $minLength) {
                return [new ValidationFailure("Data cannot be shorter than $maxLength caracters.")];
            }
        }
        $regex = $this->constraint->getRegex();
        if (null !== $regex && 1 !== preg_match("/$regex/", $data)) {
            return [new ValidationFailure("$data does not match the regex.")];
        }
        return [];
    }
}