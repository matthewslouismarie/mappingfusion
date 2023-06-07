<?php

namespace MF\Model;
use InvalidArgumentException;
use Stringable;

class SlugFilename implements Stringable
{
    const REGEX = '^(([a-z0-9])-?)*(?2)+\.(?2)+$';

    private LongString $value;

    public function __construct(string $value, bool $transform = false) {
        if ($transform) {
            $this->value = new LongString(preg_replace('/[^a-z0-9\-\.]/', '', preg_replace('/[_\s]/', '-', strtolower($value))));
        } else {
            $this->value = new LongString($value);
        }
        if (1 !== preg_match('/'.self::REGEX.'/', $this->value)) {
            throw new InvalidArgumentException();
        }
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}