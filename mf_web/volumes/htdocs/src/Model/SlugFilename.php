<?php

namespace MF\Model;
use Stringable;

class SlugFilename implements Stringable
{
    const REGEX = '^(([a-z0-9])-?)*(?2)+\.(?2)+$';

    private LongString $value;

    public function __construct(string $value) {
        $this->value = new LongString(preg_replace('/[^a-z0-9\-\.]/', '', preg_replace('/[_\s]/', '-', strtolower($value))));
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}