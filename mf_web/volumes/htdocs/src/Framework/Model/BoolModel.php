<?php

namespace MF\Framework\Model;

class BoolModel implements IModel
{
    public function __construct(
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

    public function getNumberConstraints(): ?array {
        return null;
    }

    public function getStringConstraints(): ?array {
        return null;
    }

    public function isBool(): bool {
        return true;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}