<?php

namespace MF\Constraint;

interface IStringConstraint extends IType
{
    public function getMaxLength(): ?int;

    public function getMinLength(): ?int;

    public function getRegex(): ?string;
}