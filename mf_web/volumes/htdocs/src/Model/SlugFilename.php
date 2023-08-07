<?php

namespace MF\Model;

use InvalidArgumentException;
use MF\Constraint\IFileConstraint;
use Stringable;

class SlugFilename implements Stringable
{
    private LongString $value;

    public function __construct(string $value, bool $transform = false) {
        if ($transform) {
            $this->value = new LongString(preg_replace('/[^a-z0-9\-\.]/', '', preg_replace('/[_\s]/', '-', strtolower($value))));
        } else {
            $this->value = new LongString($value);
        }
        if (1 !== preg_match('/' . IFileConstraint::FILENAME_REGEX . '/', $this->value)) {
            throw new InvalidArgumentException();
        }
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}