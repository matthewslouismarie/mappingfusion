<?php

namespace MF\Framework\Model;

use MF\Framework\Type\ModelType;

class StdModel implements IModel
{
    /**
     * @param \MF\Framework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
        private ModelType $type,
        private array $constraints = [],
        private bool $isNullable = false,
    ) {
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getType(): ModelType {
        return $this->type;
    }

    public function isNullable(): bool {
        return $this->isNullable;
    }
}