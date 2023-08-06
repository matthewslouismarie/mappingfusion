<?php

namespace MF\Constraint;

class UintConstraint implements INumberConstraint
{
    public function __construct(
        private ?int $max = null,
    ) {
    }

    public function getMax(): ?int {
        return $this->max;
    }

    public function getMin(): int {
        return 0;
    }
}