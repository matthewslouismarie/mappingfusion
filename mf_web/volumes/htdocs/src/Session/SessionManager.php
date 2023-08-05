<?php

namespace MF\Session;

class SessionManager
{
    const CURRENT_MEMBER_USERNAME = "cmu";

    const CSRF = 'csrf';

    const CSRF_N_BYTES = 32;

    public function getCurrentMemberUsername(): ?string {
        if (isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME]) {
            return $_SESSION[self::CURRENT_MEMBER_USERNAME];
        } else {
            return null;
        }
    }

    public function isUserLoggedIn(): bool {
        return isset($_SESSION[self::CURRENT_MEMBER_USERNAME]) && null !== $_SESSION[self::CURRENT_MEMBER_USERNAME];
    }

    public function setCurrentMemberUsername(?string $username): void {
        $_SESSION[self::CURRENT_MEMBER_USERNAME] = $username;
    }

    public function getCsrf(): string {
        return $_SESSION[self::CSRF] ?? $_SESSION[self::CSRF] = bin2hex(random_bytes(self::CSRF_N_BYTES));
    }
}