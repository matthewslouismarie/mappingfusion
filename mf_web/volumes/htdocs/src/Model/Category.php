<?php

namespace MF\Model;

class Category implements EntityInterface
{
    private Slug $id;

    private LongString $name;

    static function fromArray(array $data): self {
        return new self(
            $data['category_id'],
            $data['category_name'],
        );
    }

    public function __construct(?string $id, string $name) {
        $this->id = null !== $id ? new Slug($id) : new Slug($name, true);
        $this->name = new LongString($name);
    }

    public function getId(): Slug {
        return $this->id;
    }

    public function getName(): LongString {
        return $this->name;
    }

    public function toArray(): array {
        return [
            'category_id' => $this->id->__toString(),
            'category_name' => $this->name->__toString(),
        ];
    }
}