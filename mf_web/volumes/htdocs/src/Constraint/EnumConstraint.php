<?php

namespace MF\Constraint;

class EnumConstraint implements IType
{
    public function __construct(
        private array $acceptedValues,
    ) {
    }

    public function getAcceptedValues(): array {
        return $this->acceptedValues;
    }
}