<?php

namespace MF\Framework\Validator;

use MF\Framework\Constraints\IUploadedImageConstraint;
use MF\Framework\DataStructures\ConstraintViolation;

class UploadedImageValidator implements IValidator
{
    public function __construct(
        private IUploadedImageConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        $violations = [];
        if (1 !== preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $data)) {
            $violations[] = new ConstraintViolation($this->constraint, 'Filename does not have the correct pattern.');
        }
        if (strlen($data) > IUploadedImageConstraint::FILENAME_MAX_LENGTH) {
            $violations[] = new ConstraintViolation($this->constraint, 'Filename is too long.');
        }
        
        return $violations;
    }
}