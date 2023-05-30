<?php

namespace MF\Model;

use LengthException;

class LongString
{
    public function __construct(string $value) {
        if (mb_strlen($value, "UTF-8") > 255) {
            throw new LengthException();
        }
    }
}