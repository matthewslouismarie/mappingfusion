<?php

namespace MF\Constraint;

class TextConstraint implements IStringConstraint
{
    public function __construct(
        private ?int $max = null,
        private ?int $min = null,
    ) {
    }

    public function getMaxLength(): ?int {
        return $this->max;
    }

    public function getMinLength(): ?int {
        return $this->min;
    }

    public function getRegex(): ?string {
        return null;
    }
}