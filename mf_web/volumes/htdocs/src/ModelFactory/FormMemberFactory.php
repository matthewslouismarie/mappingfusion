<?php

namespace MF\ModelFactory;

use MF\Model\LongString;
use MF\Model\Member;

class FormMemberFactory
{
    public function createFromRequest(array $formData): Member {
        return new Member(password_hash($formData['password'], PASSWORD_DEFAULT), $formData['username']);
    }
}