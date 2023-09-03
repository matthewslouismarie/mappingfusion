<?php

namespace MF\Model;

use MF\Exception\InvalidStringException;
use Stringable;

/**
 * @todo Replace with UrlConstraint.
 */
class Url implements Stringable
{
    const MAX_LENGTH = 255;

    private string $value;

    public function __construct(string $value) {
        if (mb_strlen($value) > self::MAX_LENGTH) {
            throw new InvalidStringException();
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}