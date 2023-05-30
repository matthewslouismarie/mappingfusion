<?php

namespace MF\ModelFactory;

use MF\Model\LongString;
use MF\Model\Member;

class FormMemberFactory
{
    public function createFromRequest(array $formData): Member {
        return new Member(new LongString($formData['username']), new LongString(password_hash($formData['password'], PASSWORD_DEFAULT)));
    }
}