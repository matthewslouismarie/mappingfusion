<?php

namespace MF\Constraint;

class ForeignKey implements IConstraint
{
    public function __construct(
        private string $modelName,
        private string $propertyName,
    ) {
    }

    public function getModelName(): string {
        return $this->modelName;
    }

    public function getPropertyName(): string {
        return $this->propertyName;
    }
}