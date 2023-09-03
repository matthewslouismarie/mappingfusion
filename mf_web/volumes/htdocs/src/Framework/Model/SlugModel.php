<?php

namespace MF\Framework\Model;

use MF\Framework\Constraints\StringConstraint;

class SlugModel extends StringModel
{
    /**
     * @param \MF\Framework\Constraints\IConstraint[] $constraints
     */
    public function __construct(
    ) {
        parent::__construct([new StringConstraint(minLength: 1, regex: StringConstraint::REGEX_DASHES)]);
    }
}