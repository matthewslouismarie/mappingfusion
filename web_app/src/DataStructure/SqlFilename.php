<?php

namespace MF\DataStructure;

use InvalidArgumentException;
use Stringable;

/**
 * @todo Crease similar class Filename and move it to lm-web-framework?
 */
class SqlFilename implements Stringable
{
    private string $filename;

    public function __construct(string $filename)
    {
        if (1 !== preg_match('/^(\w+_)*\w+\.sql+$/', $filename)) {
            throw new InvalidArgumentException('String argument is not a valid filename.');
        }

        $this->filename = $filename;
    }

    public function __toString(): string
    {
        return $this->filename;
    }
}