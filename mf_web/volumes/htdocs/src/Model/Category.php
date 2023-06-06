<?php

namespace MF\Model;

class Category
{
    private Slug $id;

    private LongString $name;

    static function fromArray(array $data): self {
        return new self(
            null !== $data['p_id'] ? new Slug($data['p_id']) : null,
            new LongString($data['p_name']),
        );
    }

    public function __construct(?Slug $id, LongString $name) {
        $this->id = $id ?? new Slug($name);
        $this->name = $name;
    }

    public function getId(): Slug {
        return $this->id;
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