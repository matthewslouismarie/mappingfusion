<?php

namespace MF\Model;

class Member implements Entity
{
    private LongString $id;

    private PasswordHash $passwordHash;

    public function __construct(
        string $id,
        PasswordHash $passwordHash,
    ) {
        $this->id = new LongString($id);
        $this->passwordHash = $passwordHash;
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getPasswordHash(): PasswordHash {
        return $this->passwordHash;
    }

    public function setPasswordHash(PasswordHash $passwordHash): Member {
        return new Member($this->id, $passwordHash);
    }

    public function toArray(): array {
        return [
            'member_id' => $this->id->__toString(),
            'member_password_hash' => $this->passwordHash->__toString(),
        ];
    }
}