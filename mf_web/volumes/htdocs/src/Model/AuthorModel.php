<?php

namespace MF\Model;

use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\StringModel;

/**
 * @todo Add knowsAbout, memberOf
 * @todo Rename to Person
 */
class AuthorModel extends AbstractEntity
{
    public function __construct(?MemberModel $memberModel = null) {
        $properties = [
            'id' => new StringModel([
                new StringConstraint(regex: StringConstraint::REGEX_DASHES),
            ]),
            'name' => new StringModel([
                new StringConstraint(minLength: 1),
            ]),
            'avatar_filename' => new StringModel([
                new class implements IUploadedImageConstraint {}
            ], true),
        ];
        if (null !== $memberModel) {
            $properties['member'] = $memberModel;
        }
        parent::__construct($properties);
    }
}