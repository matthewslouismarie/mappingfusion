<?php

namespace MF\Model;

use DomainException;
use Stringable;

class Uint implements Stringable
{
    private int $value;

    public function __construct(int|string $value) {
        if ($value < 0) {
            throw new DomainException();
        }
        if (!is_numeric($value)) {
            throw new DomainException();
        }
        $this->value = $value;
    }

    public function __toString(): string {
        return strval($this->value);
    }

    public function toInt(): int {
        return $this->value;
    }
}