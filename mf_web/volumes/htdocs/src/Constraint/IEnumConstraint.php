<?php

namespace MF\Constraint;

interface IEnumConstraint
{
    /**
     * @return mixed[] List of all the accepted values.
     */
    public function getAcceptedValues(): array;
}