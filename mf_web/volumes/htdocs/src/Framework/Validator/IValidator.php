<?php

namespace MF\Framework\Validator;

interface IValidator
{
    /**
     * @return \MF\Framework\DataStructures\ConstraintViolation[]
     */
    public function validate(mixed $data): array;
}