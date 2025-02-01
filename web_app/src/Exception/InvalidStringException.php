<?php

namespace MF\Exception;

use DomainException;
use Throwable;

class InvalidStringException extends DomainException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(
            message: "The given string’s length is not within valid limits.",
            previous: $previous,
        );
    }
}