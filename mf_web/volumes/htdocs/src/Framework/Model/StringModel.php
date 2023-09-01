<?php

namespace MF\Framework\Model;

class StringModel implements IModel
{
    /**
     * @param \MF\Framework\Constraints\IConstraint[] $constraints
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
        return null;
    }

    public function getListNodeModel(): ?IModel {
        return null;
    }

    public function getStringConstraints(): array {
        return $this->constraints;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}