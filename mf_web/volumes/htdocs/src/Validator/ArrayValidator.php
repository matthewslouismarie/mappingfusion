<?php

namespace MF\Validator;

use MF\Constraint\IArrayConstraint;

class ArrayValidator implements IValidator
{
    public function __construct(
        private IArrayConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }
        if (!is_array($data)) {
            var_dump($data);
            return [new ValidationFailure('Array data is not an array.')];
        }
        $validator = (new ValidatorFactory())->createValidator($this->constraint->getElementType());
        $failures = [];
        foreach ($data as $value) {
            $validation = $validator->validate($value);
            if (null !== $validation) {
                $failures += $validation;
            }
        }
        return $failures;
    }
}