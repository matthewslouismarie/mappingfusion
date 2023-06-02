<?php

namespace MF\Model;

class Author implements Entity
{
    private Slug $id;

    private LongString $name;

    static function fromArray(array $data): self {
        return new self(
            $data['p_id'],
            $data['p_name'],
        );
    }

    public function __construct(
        string $id,
        string $name,
    ) {
        $this->id = new Slug($id);
        $this->name = new LongString($name);
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_name' => $this->name->__toString(),
        ];
    }
}