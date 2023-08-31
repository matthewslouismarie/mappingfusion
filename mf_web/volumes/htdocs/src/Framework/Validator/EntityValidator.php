<?php

namespace MF\Framework\Validator;

use MF\Framework\Constraints\EntityConstraint;
use MF\Framework\DataStructures\ConstraintViolation;
use MF\Framework\Type\ModelValidator;

class EntityValidator implements IValidator
{
    public function __construct(
        private EntityConstraint $constraint,
        private ModelValidator $modelValidator,
    ) {
    }

    public function validate(mixed $data): array {
        $cvs = [];
        foreach ($this->constraint->getProperties() as $key => $property) {
            $violations = $this->modelValidator->validate($data[$key], $property);
            if (count($violations) > 0) {
                $cvs = array_merge_recursive($cvs, $violations);
            }
        }
        if (count($this->constraint->getProperties()) < count($data)) {
            $cvs[] = new ConstraintViolation($this->constraint, 'Non-defined properties are not allowed.');
        }
        return $cvs;
    }
}