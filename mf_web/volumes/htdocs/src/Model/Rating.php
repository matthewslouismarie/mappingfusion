<?php

namespace MF\Model;

use DomainException;

class Rating
{
    private float $rating;

    public function __construct(float $rating) {
        if ($rating > 5 || $rating < 1) {
            throw new DomainException();
        }

        $this->rating = $rating;
    }

    public function toFloat(): float {
        return $this->rating;
    }
}