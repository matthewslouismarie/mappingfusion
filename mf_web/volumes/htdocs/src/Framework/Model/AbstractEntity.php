<?php

namespace MF\Framework\Model;

abstract class AbstractEntity implements IModel
{
    public function __construct(
        private bool $nullable = false,
    ) {
    }

    public function getDateTimeConstraints(): ?array {
        return null;
    }

    public function getListNodeModel(): ?IModel {
        return null;
    }

    public function getStringConstraints(): ?array {
        return null;
    }

    public function isBool(): bool {
        return false;
    }

    public function isNullable(): bool {
        return $this->nullable;
    }
}