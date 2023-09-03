<?php

namespace MF\Model;

use MF\Framework\Constraints\RangeConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\BoolModel;
use MF\Framework\Model\NumberModel;
use MF\Framework\Model\StringModel;

class ContributionModel extends AbstractEntity
{
    public function getArrayDefinition(): array {
        return [
            'id' => new NumberModel([new RangeConstraint(0)], true),
            'author_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'playable_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'is_author' => new BoolModel(),
            'summary' => new StringModel([new StringConstraint(maxLength: null)], isNullable: true),
        ];
    }
}