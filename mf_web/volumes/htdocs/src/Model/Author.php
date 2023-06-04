<?php

namespace MF\Model;

class Author implements Entity
{
    private Slug $id;

    private LongString $name;

    static function fromArray(array $data): self {
        return new self(
            $data['p_id'] ?? null,
            $data['p_name'],
        );
    }

    public function __construct(
        ?string $id,
        string $name,
    ) {
        $this->id = $id !== null ? new Slug($id) : new Slug($name);
        $this->name = new LongString($name);
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getName(): LongString {
        return $this->name;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_name' => $this->name->__toString(),
        ];
    }
}