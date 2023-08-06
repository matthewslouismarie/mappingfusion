<?php

namespace MF\Model;

use MF\Enum\ModelPropertyType;
use MF\Model\ModelProperty;

class PlayableLinkDefinition implements IModelDefinition
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', ModelPropertyType::UINT, isGenerated: true),
            new ModelProperty('playable_id', ModelPropertyType::VARCHAR),
            new ModelProperty('name', ModelPropertyType::VARCHAR),
            new ModelProperty('type', ModelPropertyType::VARCHAR),
            new ModelProperty('url', ModelPropertyType::VARCHAR),
        ];
    }
}