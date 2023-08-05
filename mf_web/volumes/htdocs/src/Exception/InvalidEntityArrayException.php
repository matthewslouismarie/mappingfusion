<?php

namespace MF\Exception;

use DomainException;
use Throwable;

class InvalidEntityArrayException extends DomainException
{
    public function __construct(string $class, ?Throwable $previous = null, ?string $property = null) {
        parent::__construct(
            message: "The given array does not have the expected keys and/or its values are not of the correct type or format for $class $property.",
            previous: $previous,
        );
    }
}