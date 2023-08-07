<?php

namespace MF\DataStructure;
use InvalidArgumentException;

class DecimalNumber
{
    private int $numerator;

    private int $decimalPower;

    public function __construct(int|string $number) {
        if (is_int($number)) {
            $this->numerator = $number;
            $this->decimalPower = 0;
        } else {
            $matches = [];
            preg_match('/^([0-9]+)(\.([0-9]+))?$/', $number, $matches);
            if (4 !== count($matches)) {
                throw new InvalidArgumentException("$number is not a numeric string.");
            }
            $this->numerator = intval($matches[1] . $matches[3]);
            $this->decimalPower = strlen($matches[3]);
        }
    }

    public function getDecimalPart(): string {
        return 0 === $this->decimalPower ? '' : substr(sprintf('%d', $this->numerator), -$this->decimalPower);
    }

    public function getDecimalPower(): int {
        return $this->decimalPower;
    }

    public function getIntegralPart(): string {
        $integralPart = substr(sprintf('%d', $this->numerator), 0, strlen(sprintf('%d', $this->numerator)) - $this->decimalPower);
        return '' === $integralPart ? '0' : $integralPart;
    }

    public function getNumerator(): int {
        return $this->numerator;
    }

    public function toFloat(): float {
        return $this->numerator / 10**$this->decimalPower;
    }

    public function toString(): string {
        return $this->getIntegralPart() . '.' . $this->getDecimalPart();
    }
}