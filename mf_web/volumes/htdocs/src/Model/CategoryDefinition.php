<?php

namespace MF\Model;
use MF\Enum\ModelPropertyType;

class CategoryDefinition implements ModelDefinition
{
    public function __construct(
        private string $name = 'category',
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

    public function getStoredData(): array {
        return [];
    }
}