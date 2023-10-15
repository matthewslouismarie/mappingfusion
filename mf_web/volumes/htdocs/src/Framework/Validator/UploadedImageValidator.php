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
                $violations[] = new ConstraintViolation($this->constraint, 'Le nom du fichier est trop long.');
            }
            $nameParts = explode('.', $data);
            
            if (1 !== preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $data)) {
                $violations[] = new ConstraintViolation($this->constraint, 'Le nom du fichier n’a pas le bon format.');
            }
        }
        
        return $violations;
    }
}