<?php

namespace MF\Exception\InvalidFormException;

use InvalidArgumentException;

/**
 * Thrown when the submittable could not find any value from the request.
 */
class MissingInputException extends InvalidArgumentException implements InvalidFormException
{
    public function __construct($message = "", $code = 0, $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function getUserErrorMessage(): string {
        return 'Une erreur s’est produite. ' . self::class;
    }
}