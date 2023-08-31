<?php

namespace MF\Framework\DataStructures;

use MF\Framework\Constraints\IConstraint;

class ConstraintViolation
{
    public function __construct(
        private IConstraint $constraint,
        private ?string $message = null,
    ) {
    }
}