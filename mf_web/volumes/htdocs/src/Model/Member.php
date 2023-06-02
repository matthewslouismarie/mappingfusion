<?php

namespace MF\Model;

class Member implements Entity
{
    private LongString $passwordHash;
    private LongString $username;

    public function __construct(
        string $passwordHash,
        string $username,
    ) {
        $this->passwordHash = new LongString($passwordHash);
        $this->username = new LongString($username);
    }

    public function getId(): string {
        return $this->username;
    }

    public function getPasswordHash(): LongString {
        return $this->passwordHash;
    }

    public function getUsername(): LongString {
        return $this->username;
    }

    public function setPasswordHash(string $passwordHash): Member {
        return new Member($passwordHash, $this->username);
    }

    public function toArray(): array {
        return [
            'p_username' => $this->username,
            'p_password_hash' => $this->passwordHash,
        ];
    }
}