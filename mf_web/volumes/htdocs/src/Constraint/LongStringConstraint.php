<?php

namespace MF\Constraint;

class LongStringConstraint implements IStringConstraint
{
    const MAX_LENGTH = 255;

    public function getMaxLength(): int {
        return self::MAX_LENGTH;
    }

    public function getMinLength(): ?int {
        return null;
    }

    public function getRegex(): ?string {
        return null;
    }
}