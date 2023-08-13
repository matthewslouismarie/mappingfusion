<?php

namespace MF\Constraint;

class EnumConstraint implements IEnumConstraint
{
    private array $acceptedValues;

    public function __construct(
        array $enumCases,
    ) {
        $this->acceptedValues = [];
        foreach ($enumCases as $c) {
            $this->acceptedValues[] = $c->value;
        }
    }

    public function getAcceptedValues(): array {
        return $this->acceptedValues;
    }
}