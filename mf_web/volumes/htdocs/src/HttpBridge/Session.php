<?php

namespace MF\HttpBridge;

use MF\Model\LongString;

class Session
{
    const CURRENT_MEMBER_USERNAME = "cmu";

    public function getCurrentMemberUsername(): ?LongString {
        if (isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME]) {
            return new LongString($_SESSION[self::CURRENT_MEMBER_USERNAME]);
        } else {
            return null;
        }
    }

    public function isUserLoggedIn(): bool {
        return isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME];
    }

    public function setCurrentMemberUsername(?LongString $username): void {
        $_SESSION[self::CURRENT_MEMBER_USERNAME] = $username?->__toString();
    }
}