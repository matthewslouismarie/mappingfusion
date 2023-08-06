<?php

namespace MF\Exception\InvalidFormException;

use Throwable;

/**
 * Thrown by a submittable when no value could be extracted from the request.
 */
interface InvalidFormException extends Throwable
{
    /**
     * @return string A message to display to the user.
     */
    public function getUserErrorMessage(): string;
}