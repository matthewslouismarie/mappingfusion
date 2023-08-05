<?php

namespace MF\Exception\InvalidFormException;

use InvalidArgumentException;

class InvalidInputException extends InvalidArgumentException implements InvalidFormException
{
    private string $userErrorMessage;

    public function __construct(string $userErrorMessage) {
        $this->userErrorMessage = $userErrorMessage;
        parent::__construct();
    }

    public function getUserErrorMessage(): string {
        return $this->userErrorMessage;
    }
}