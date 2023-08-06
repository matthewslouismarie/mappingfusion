<?php

namespace MF\Constraint;

interface INumberConstraint extends IType
{
    public function getMin(): ?int;

    public function getMax(): ?int;
}