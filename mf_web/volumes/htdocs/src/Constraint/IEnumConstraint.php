<?php

namespace MF\Constraint;

interface IEnumConstraint extends IType
{
    /**
     * @return mixed[] List of all the accepted values.
     */
    public function getAcceptedValues(): array;
}