<?php

namespace MF\Model;

use MF\Framework\Constraints\StringConstraint;
use Stringable;
use UnexpectedValueException;

class Slug implements Stringable
{
    private string $value;

    public function __construct(string $value, bool $transform = false, bool $allowEmpty = false) {
        if ($transform) {
            $this->value = substr(preg_replace('/[^a-z0-9\-]|(--)|(^-)|(-$)/', '', preg_replace('/[ _]|(--)/', '-', strtolower($value))), 0, StringConstraint::MAX_LENGTH);
        } else {
            $this->value = $value;
        }
        if (!$allowEmpty && (0 === strlen($this->value) || 1 !== preg_match('/' . StringConstraint::REGEX_DASHES . '/', $this->value))) {
            throw new UnexpectedValueException($this->value);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}