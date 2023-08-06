<?php

namespace MF\Constraint;

interface IArrayConstraint extends IType
{
    public function getElementType(): IType;
}