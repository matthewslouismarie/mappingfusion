<?php

namespace MF\Model;

use LM\WebFramework\Constraints\RangeConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\BoolModel;
use LM\WebFramework\Model\IntegerModel;
use LM\WebFramework\Model\StringModel;

class ContributionModel extends AbstractEntity
{
    public function __construct(?AuthorModel $authorModel = null) {
        $properties = [
            'id' => new IntegerModel([new RangeConstraint(0)], true),
            'author_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'playable_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'is_author' => new BoolModel(),
            'summary' => new StringModel([new StringConstraint(maxLength: null)], isNullable: true),
        ];

        if (null !== $authorModel) {
            $properties['author'] = $authorModel;
        }

        parent::__construct($properties);
    }
}