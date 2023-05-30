<?php

namespace MF\Model;

class Member
{
    public function __construct(
        private LongString $username,
        private LongString $passwordHash,
    ) {
    }
}