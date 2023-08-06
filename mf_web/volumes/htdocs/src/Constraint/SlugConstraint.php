<?php

namespace MF\Constraint;

use MF\Constraint\IStringConstraint;

class SlugConstraint implements IStringConstraint
{
    const REGEX_DASHES = '^(([a-z0-9])-?)*(?2)+$';

    const REGEX_UNDERSCORES = '^(([a-z0-9]+)_)*(?2)$';

    public function __construct(
        private bool $dashes = true,
        private ?int $maxLength = LongStringConstraint::MAX_LENGTH,
        private int $minLength = 1,
    ) {
    }

    public function getMaxLength(): ?int {
        return $this->maxLength;
    }

    public function getMinLength(): int {
        return $this->minLength;
    }

    public function getRegex(): string {
        if ($this->dashes) {
            return self::REGEX_DASHES;
        } else {
            return self::REGEX_UNDERSCORES;
        }
    }
}