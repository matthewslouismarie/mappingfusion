<?php

namespace MF\Constraint;

interface IDecimalConstraint extends IType
{
    const REGEX = '/^([0-9]+)(\.(?1))?$/';

    /**
     * Minimum numerator of the decimal number.
     */
    public function getMin(): ?int;

    /**
     * Maximum numerator of the decimal number.
     */
    public function getMax(): ?int;

    /**
     * Denominator of the decimal number.
     */
    public function getDecimalPower(): int;
}