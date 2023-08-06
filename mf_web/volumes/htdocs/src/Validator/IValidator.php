<?php

namespace MF\Validator;

interface IValidator
{
    /**
     * @return ValidationFailure[]
     */
    public function validate(mixed $data): array;
}