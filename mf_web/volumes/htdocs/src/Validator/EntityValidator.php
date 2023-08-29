<?php

namespace MF\Validator;

use MF\Constraint\IModel;
use MF\Form\FormArray;
use OutOfBoundsException;

class EntityValidator implements IValidator
{
    public function __construct(
        private IModel $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (!is_array($data) && !$data instanceof FormArray) {
            return [new ValidationFailure('Entity data is not an array.')];
        }
        if ($data instanceof FormArray) {
            return $data->getErrors();
        }
        $validatorFactory = new ValidatorFactory();
        $failures = [];
        foreach ($this->constraint->getProperties() as $p) {
            try {
                foreach ($p->getConstraints() as $c) {
                    $validationFailure = $validatorFactory->createValidator($c)->validate($data[$p->getName()]);
                    if ([] !== $validationFailure) {
                        $failures += $validationFailure;
                    }
                }
            } catch (OutOfBoundsException $e) {
                $failures[] = new ValidationFailure('Some of the properties are missing.');
            }
        }
        return $failures;
    }
}