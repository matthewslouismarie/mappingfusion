<?php

namespace MF\Model;

use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\StringModel;

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