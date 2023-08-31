<?php

namespace MF\Framework\Constraints;

class StringConstraint implements IConstraint
{
    const MAX_LENGTH = 255;

    const REGEX_DASHES = '^(([a-z0-9])-?)*(?2)+$';

    public function __construct(
        private int $minLength = 0,
        private ?int $maxLength = null,
        private ?string $regex = null,
    ) {
    }

    public function getMinLength(): int {
        return $this->minLength;
    }

    public function getMaxLength(): ?int {
        return $this->maxLength;
    }

    public function getRegex(): ?string {
        return $this->regex;
    }
}