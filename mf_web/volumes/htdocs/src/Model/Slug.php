<?php

namespace MF\Model;

use Stringable;
use UnexpectedValueException;

class Slug implements Stringable
{
    const REGEX = '^(([a-z0-9])-?)*(?2)+$';

    private LongString $value;

    public function __construct(string $value, bool $transform = false) {
        if ($transform) {
            $this->value = new LongString(substr(preg_replace('/[^a-z0-9\-]|(--)|(^-)|(-$)/', '', preg_replace('/[ _]|(--)/', '-', strtolower($value))), 0, LongString::MAX_LENGTH));
        } else {
            $this->value = new LongString($value);
        }
        if (0 === strlen($this->value) || 1 !== preg_match('/'.self::REGEX.'/', $this->value)) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}