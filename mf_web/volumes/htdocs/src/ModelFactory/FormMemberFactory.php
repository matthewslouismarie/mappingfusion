<?php

namespace MF\ModelFactory;

use MF\Model\LongString;
use MF\Model\Member;
use MF\Model\PasswordHash;

class FormMemberFactory
{
    public function createFromRequest(array $formData): Member {
        return new Member($formData['username'], new PasswordHash(clear: $formData['password']));
    }
}