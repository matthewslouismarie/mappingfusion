<?php

namespace MF\Constraint;

class DecimalConstraint implements IDecimalConstraint
{
    public function __construct(
        private ?int $max = null,
        private ?int $min = null,
        private int $decimalPower = 0,
    ) {
    }

    public function getDecimalPower(): int {
        return $this->decimalPower;
    }

    public function getMax(): ?int {
        return $this->max;
    }

    public function getMin(): ?int {
        return $this->min;
    }
}