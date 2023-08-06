<?php

namespace MF\Model;

use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;
use Stringable;
use UnexpectedValueException;

class Slug implements Stringable
{

    private LongString $value;

    public function __construct(string $value, bool $transform = false) {
        if ($transform) {
            $this->value = new LongString(substr(preg_replace('/[^a-z0-9\-]|(--)|(^-)|(-$)/', '', preg_replace('/[ _]|(--)/', '-', strtolower($value))), 0, LongStringConstraint::MAX_LENGTH));
        } else {
            $this->value = new LongString($value);
        }
        if (0 === strlen($this->value) || 1 !== preg_match('/' . SlugConstraint::REGEX_DASHES . '/', $this->value)) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value->__toString();
    }
}