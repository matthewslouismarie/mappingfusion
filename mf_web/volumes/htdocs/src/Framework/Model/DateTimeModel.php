<?php

namespace MF\Framework\Model;

class DateTimeModel implements IModel
{
    /**
     * @param \MF\Framework\Constraints\IDateTimeConstraint[] $constraints
     */
    public function __construct(
        private array $constraints = [],
        private bool $isNullable = false,
    ) {
    }

    public function getArrayDefinition(): ?array {
        return null;
    }

    public function getDateTimeConstraints(): ?array {
        return $this->constraints;
    }

    public function getListNodeModel(): ?IModel {
        return null;
    }

    public function getIntegerConstraints(): ?array {
        return null;
    }

    public function getStringConstraints(): ?array {
        return null;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}