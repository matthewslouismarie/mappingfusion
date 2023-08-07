<?php

namespace MF\Validator;

use InvalidArgumentException;
use MF\Constraint\IDecimalConstraint;
use MF\DataStructure\DecimalNumber;

class DecimalNumberValidator implements IValidator
{
    public function __construct(
        private IDecimalConstraint $constraint,
    ) {
    }

    public function validate(mixed $data): array {
        if (null === $data) {
            return [];
        }

        try {
            $number = new DecimalNumber($data);
        } catch (InvalidArgumentException $e) {
            return [new ValidationFailure("Data is not a decimal number (neither int nor formatted string.)")];
        }
        $dataSameDenominator = $number->getNumerator() * 10**$this->constraint->getDecimalPower();
        if (null !== $this->constraint->getMax()) {
            $maxSameDenominator = $this->constraint->getMax() * 10**$number->getDecimalPower();
            if ($dataSameDenominator > $maxSameDenominator) {
                return [new ValidationFailure("$dataSameDenominator cannot be higher than $maxSameDenominator.")];
            }
        }
        $min = $this->constraint->getMin();
        if (null !== $min && $dataSameDenominator < $min * 10**$number->getDecimalPower()) {
            return [new ValidationFailure("$dataSameDenominator cannot be lower than $min.")];
        }
        return [];
    }
}