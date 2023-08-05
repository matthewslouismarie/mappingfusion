<?php

namespace MF\Exception\InvalidFormException;

use Throwable;

interface InvalidFormException extends Throwable
{
    public function getUserErrorMessage(): string;
}