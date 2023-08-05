<?php

namespace MF\Model;

use MF\Enum\ModelPropertyType;

class AuthorDefinition implements ModelDefinition
{
    public function __construct(
        private string $name = 'author',
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', ModelPropertyType::VARCHAR),
            new ModelProperty('name', ModelPropertyType::VARCHAR),
        ];
    }
}