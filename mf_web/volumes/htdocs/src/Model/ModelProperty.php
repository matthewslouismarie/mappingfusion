<?php

namespace MF\Model;

use MF\Enum\ModelPropertyType;

class ModelProperty implements IModelProperty
{
    private string $name;

    private ModelPropertyType $type;

    private $isGenerated;

    private $isRequired;

    private array $constraints;

    private array $references;

    public function __construct(
        string $name,
        ModelPropertyType $type,
        array $constraints = [],
        bool $isGenerated = false,
        bool $isRequired = true,
        array $references = [],
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->constraints = $constraints;
        $this->isGenerated = $isGenerated;
        $this->isRequired = $isRequired;
        $this->references = $references;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): ModelPropertyType {
        return $this->type;
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getReferenceName(IModelDefinition $definition): ?string {
        return $this->references[get_class($definition)] ?? null;
    }

    public function isGenerated(): bool {
        return $this->isGenerated;
    }

    public function isRequired(): bool {
        return $this->isRequired;
    }
}