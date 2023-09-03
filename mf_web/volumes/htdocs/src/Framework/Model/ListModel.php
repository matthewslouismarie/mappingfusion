<?php

namespace MF\Framework\Model;

class ListModel implements IModel
{
    /**
     * @param \MF\Framework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        private IModel $nodeModel,
        private bool $isNullable = false,
    ) {
    }

    public function getArrayDefinition(): ?array {
        return null;
    }

    public function getDateTimeConstraints(): ?array {
        return null;
    }

    public function getListNodeModel(): IModel {
        return $this->nodeModel;
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