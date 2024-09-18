<?php

namespace MF\Exception\Entity;

use InvalidArgumentException;

class EntityValidationException extends InvalidArgumentException
{
    public function __construct(array $failure, $code = 0, $previous = null) {
        $errorMessage = '';
        foreach ($failure as $f) {
            $errorMessage .= $f->getMessage() . '\n\n';
        }
        parent::__construct($errorMessage, $code, $previous);
    }
}