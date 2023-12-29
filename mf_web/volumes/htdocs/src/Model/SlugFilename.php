<?php

namespace MF\Model;

use InvalidArgumentException;
use LM\WebFramework\Constraints\IUploadedImageConstraint;
use Stringable;

class SlugFilename implements Stringable
{
    private string $value;

    public function __construct(string $value, bool $transform = false) {
        if ($transform) {
            $this->value = preg_replace('/[^a-z0-9\-\.]/', '', preg_replace('/[_\s]/', '-', strtolower($value)));
        } else {
            $this->value = $value;
        }
        if (1 !== preg_match('/' . IUploadedImageConstraint::FILENAME_REGEX . '/', $this->value)) {
            throw new InvalidArgumentException();
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }
}