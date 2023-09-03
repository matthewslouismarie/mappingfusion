<?php

namespace MF\Framework\Constraints;

class EnumConstraint implements IStringConstraint
{
    private array $values;

    /**
     * @param string[] $values
     */
    public function __construct(
        private array $enumCases,
    ) {
        $this->values = [];
        foreach ($enumCases as $c) {
            $this->values[] = $c->value;
        }
    }

    public function getValues(): array {
        return $this->values;
    }
}