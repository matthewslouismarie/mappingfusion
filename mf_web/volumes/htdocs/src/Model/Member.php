<?php

namespace MF\Model;

class Member
{
    public function __construct(
        private LongString $username,
        private LongString $passwordHash,
    ) {
    }

    public function getPasswordHash(): LongString {
        return $this->passwordHash;
    }

    public function getUsername(): LongString {
        return $this->username;
    }
}