<?php

namespace MF\Model;

use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\StringModel;

class MemberModel extends AbstractEntity
{
    public function __construct(?AuthorModel $authorModel = null) {
        $properties = [
            'id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ]),
            'password' => new StringModel([
                new StringConstraint(),
            ]),
            'author_id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ], true),
        ];
        if (null !== $authorModel) {
            $properties['author'] = $authorModel;
        }
        parent::__construct($properties);
    }
}