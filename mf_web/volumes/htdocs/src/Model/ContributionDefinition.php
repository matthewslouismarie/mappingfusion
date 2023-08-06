<?php

namespace MF\Model;
use MF\Enum\ModelPropertyType;

class ContributionDefinition implements IModelDefinition
{
    public function __construct(
        private string $name = 'contribution',
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', ModelPropertyType::UINT, isGenerated: true),
            new ModelProperty('author_id', ModelPropertyType::VARCHAR),
            new ModelProperty('playable_id', ModelPropertyType::VARCHAR),
            new ModelProperty('is_author', ModelPropertyType::BOOL),
            new ModelProperty('summary', ModelPropertyType::VARCHAR, isRequired: false),
        ];
    }
}