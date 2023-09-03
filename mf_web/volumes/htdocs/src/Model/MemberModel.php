<?php

namespace MF\Model;

use MF\Framework\Constraints\EntityConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\StringModel;

class MemberModel extends AbstractEntity
{
    public function getArrayDefinition(): array {
        return [
            'id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ]),
            'password' => new StringModel([
                new StringConstraint(),
            ]),
        ];
    }
}