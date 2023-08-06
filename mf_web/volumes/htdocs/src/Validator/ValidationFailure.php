<?php

namespace MF\Validator;

class ValidationFailure
{
    public function __construct(
        private string $message,
    ) {
    }

    public function getMessage(): string {
        return $this->message;
    }
}