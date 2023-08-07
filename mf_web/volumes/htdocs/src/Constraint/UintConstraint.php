<?php

namespace MF\Constraint;

class UintConstraint implements IDecimalConstraint
{
    public function __construct(
        private ?int $max = null,
        private ?int $min = 0,
    ) {
    }

    public function getMax(): ?int {
        return $this->max;
    }

    public function getMin(): int {
        return $this->min;
    }

    public function getDecimalPower(): int {
        return 0;
    }
}