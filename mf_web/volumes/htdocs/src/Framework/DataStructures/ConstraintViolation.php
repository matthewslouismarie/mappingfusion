<?php

namespace MF\Framework\DataStructures;

use MF\Framework\Constraints\IConstraint;
use Stringable;

class ConstraintViolation implements Stringable
{
    public function __construct(
        private IConstraint $constraint,
        private ?string $message = null,
    ) {
    }

    public function getMessage(): ?string {
        return $this->message;
    }

    public function __toString(): string {
        return $this->message ?? $this->constraint::class . 'failed.';
    }
}