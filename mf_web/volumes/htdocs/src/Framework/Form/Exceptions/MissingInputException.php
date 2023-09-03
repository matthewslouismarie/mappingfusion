<?php

namespace MF\Framework\Form\Exceptions;

/**
 * Thrown when the submittable could not find any value from the request.
 */
class MissingInputException extends ExtractionException
{
    public function __construct(?string $inputName = null, $previous = null) {
        parent::__construct(null !== $inputName ? $inputName . ' is missing ' : null, previous: $previous);
    }

    public function getUserErrorMessage(): string {
        return 'Une erreur s’est produite. ' . self::class;
    }
}