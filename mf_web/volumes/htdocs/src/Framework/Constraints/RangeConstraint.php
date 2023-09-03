<?php

namespace MF\Framework\Constraints;

class RangeConstraint implements INumberConstraint
{
    public function __construct(
        private ?int $min = 0,
        private ?int $max = null,
    ) {
    }

    public function getMin(): ?int {
        return $this->min;
    }

    public function getMax(): ?int {
        return $this->max;
    }
}