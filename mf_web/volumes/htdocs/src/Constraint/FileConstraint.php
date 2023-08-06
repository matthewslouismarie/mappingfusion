<?php

namespace MF\Constraint;

class FileConstraint implements IFileConstraint
{

    public function getMaxLength(): int {
        return self::FILENAME_MAX_LENGTH;
    }

    public function getMinLength(): ?int {
        return null;
    }

    public function getRegex(): string {
        return self::FILENAME_REGEX;
    }
}