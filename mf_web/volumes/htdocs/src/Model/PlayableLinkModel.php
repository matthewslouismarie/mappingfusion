<?php

namespace MF\Model;

use MF\Enum\LinkType;
use MF\Framework\Constraints\EnumConstraint;
use MF\Framework\Constraints\RangeConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\IntegerModel;
use MF\Framework\Model\StringModel;

/**
 * @todo Custom constraint for type and url.
 */
class PlayableLinkModel extends AbstractEntity
{
    public function __construct() {
        parent::__construct([
            'id' => new IntegerModel([new RangeConstraint()], true),
            'playable_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'name' => new StringModel([new StringConstraint()]),
            'type' => new StringModel([new EnumConstraint(LinkType::cases())]),
            'url' => new StringModel([new StringConstraint()]),
        ]);
    }
}