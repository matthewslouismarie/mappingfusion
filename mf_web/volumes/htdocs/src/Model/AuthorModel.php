<?php

namespace MF\Model;

use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\StringModel;

class AuthorModel extends AbstractEntity
{
    public function getArrayDefinition(): array {
        return [
            'id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ]),
            'name' => new StringModel([
                new StringConstraint(),
            ]),
        ];
    }
}