<?php

namespace MF\Model;

use DomainException;

class Rating
{
    private int $rating;

    public function __construct(int $rating) {
        if ($rating > 5 || $rating < 1) {
            throw new DomainException();
        }

        $this->rating = $rating;
    }

    public function toInt(): int {
        return $this->rating;
    }
}