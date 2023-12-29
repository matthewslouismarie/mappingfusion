<?php

namespace MF\Model;

use MF\Enum\LinkType;
use LM\WebFramework\Constraints\EnumConstraint;
use LM\WebFramework\Constraints\RangeConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IntegerModel;
use LM\WebFramework\Model\StringModel;

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