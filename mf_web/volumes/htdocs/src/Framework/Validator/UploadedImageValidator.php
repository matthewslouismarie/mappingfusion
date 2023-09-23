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

    /**
     * @todo Refactor.
     * @todo Check that the image does not already exist.
     */
    public function validate(mixed $data): array {
        $violations = [];
        if (is_array($data)) {

        } else {
            if (strlen($data) > IUploadedImageConstraint::FILENAME_MAX_LENGTH) {
                $violations[] = new ConstraintViolation($this->constraint, 'Filename is too long.');
            }
        }
        
        return $violations;
    }
}