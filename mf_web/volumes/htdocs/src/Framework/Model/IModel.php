<?php

namespace MF\Framework\Model;

use MF\Framework\Type\ModelType;

interface IModel
{
    /**
     * @return \MF\Framework\Constraints\IConstraint[]
     */
    public function getConstraints(): array;

    public function getType(): ModelType;

    public function isNullable(): bool;
}