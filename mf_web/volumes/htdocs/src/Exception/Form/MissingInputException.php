<?php

namespace MF\Exception\Form;

use MF\Form\IFormElement;

/**
 * Thrown when the submittable could not find any value from the request.
 */
class MissingInputException extends ExtractionException
{
    public function __construct(?IFormElement $formElement = null, $previous = null) {
        parent::__construct(null !== $formElement ? $formElement->getName() . ' is missing ' : null, previous: $previous);
    }

    public function getUserErrorMessage(): string {
        return 'Une erreur s’est produite. ' . self::class;
    }
}