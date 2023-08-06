<?php

namespace MF\Model;

use MF\Enum\ModelPropertyType;
use MF\Model\ModelProperty;

class MemberDefinition implements IModelDefinition
{
    public function __construct(
        private string $name = 'member',
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', ModelPropertyType::VARCHAR),
            new ModelProperty('password_hash', ModelPropertyType::VARCHAR, isGenerated: true),
        ];
    }
}