<?php

namespace MF\Exception\Form;

use Throwable;

/**
 * Thrown by a submittable when no value could be extracted from the request.
 */
interface ExtractionException extends Throwable
{
    /**
     * @return string A message to display to the user.
     */
    public function getUserErrorMessage(): string;
}