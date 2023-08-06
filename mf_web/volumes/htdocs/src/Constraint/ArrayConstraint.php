<?php

namespace MF\Constraint;


class ArrayConstraint implements IArrayConstraint
{
    public function __construct(
        private IType $type,
    ) {
    }

    public function getElementType(): IType {
        return $this->type;
    }
}