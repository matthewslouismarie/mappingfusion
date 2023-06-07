<?php

namespace MF\Model;

use InvalidArgumentException;
use Stringable;

class PasswordHash implements Stringable
{
    private LongString $hash;

    public function __construct(string $clear = null, string $hash = null) {
        if (null !== $clear && null === $hash) {
            $this->hash = new LongString(password_hash($clear, PASSWORD_DEFAULT));
        } elseif (null === $clear && null !== $hash) {
            $this->hash = new LongString($hash);
        } else {
            throw new InvalidArgumentException();
        }
    }

    public function __toString(): string {
        return $this->hash->__toString();
    }
}