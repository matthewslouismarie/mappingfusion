<?php

namespace MF\Model;

use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\StringModel;

/**
 * @todo Add knowsAbout, memberOf, image
 * @todo Rename to Person
 */
class AuthorModel extends AbstractEntity
{
    public function __construct() {
        parent::__construct([
            'id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ]),
            'name' => new StringModel([
                new StringConstraint(),
            ]),
        ]);
    }
}