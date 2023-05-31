<?php

namespace MF\HttpBridge;

use MF\Model\LongString;

class Session
{
    const CURRENT_MEMBER_USERNAME = "cmu";

    public function getCurrentMemberUsername(): LongString {
        return new LongString($_SESSION[self::CURRENT_MEMBER_USERNAME]);
    }

    public function setCurrentMemberUsername(LongString $username): void {
        $_SESSION[self::CURRENT_MEMBER_USERNAME] = $username->__toString();
    }
}