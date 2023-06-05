<?php

namespace MF\Model;

use LengthException;
use Stringable;

class LongString implements Stringable
{
    const MAX_LENGTH = 255;

    public function __construct(private string $value) {
        if (mb_strlen($this->value, "UTF-8") > self::MAX_LENGTH || 0 === mb_strlen($this->value, "UTF-8")) {
            throw new LengthException();
        }
    }

    public function __toString(): string {
        return $this->value;
    }
}