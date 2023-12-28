<?php

namespace MF\Framework\Model;

use MF\Framework\Constraints\StringConstraint;

class SlugModel extends StringModel
{
    public function __construct(bool $isNullable = false) {
        parent::__construct(
            [
                new StringConstraint(minLength: 1, regex: StringConstraint::REGEX_DASHES),
            ],
            $isNullable,
        );
    }
}