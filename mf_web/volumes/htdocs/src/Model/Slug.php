<?php

namespace MF\Model;

use Stringable;
use UnexpectedValueException;

class Slug implements Stringable
{
    private LongString $value;

    public function __construct(string $value) {
        $this->value = new LongString(preg_replace('/[^a-z0-9\-]/', '', preg_replace('/[ _]/', '-', strtolower($value))));
        // if (0 === strlen($this->value)) {
        //     throw new UnexpectedValueException();
        // }
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}