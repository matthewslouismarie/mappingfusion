<?php

namespace MF\Model;
use Stringable;

class SlugFilename implements Stringable
{
    private LongString $value;

    public function __construct(string $value) {
        $this->value = new LongString(preg_replace('/[^a-z0-9\-\.]/', '', strtolower($value)));
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}