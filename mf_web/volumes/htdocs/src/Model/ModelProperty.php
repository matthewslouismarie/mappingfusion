<?php

namespace MF\Model;

use MF\Constraint\IModel;
use MF\Constraint\INotNullableConstraint;
use MF\Constraint\IType;
use MF\Constraint\NotNullableConstraint;

class ModelProperty implements IModelProperty
{
    private string $name;

    private IType $type;

    private $isGenerated;

    private array $constraints;

    private array $references;

    private bool $isPersisted;

    public function __construct(
        string $name,
        IType $type,
        array $constraints = [],
        bool $isGenerated = false,
        bool $isRequired = true,
        bool $isPersisted = true,
        array $references = [],
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->constraints = array_merge([$type], $constraints);
        $this->isGenerated = $isGenerated;
        $this->references = $references;
        $this->isPersisted = $isPersisted;
        if ($isRequired) {
            $this->constraints[] = new NotNullableConstraint();
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): IType {
        return $this->type;
    }

    public function getConstraints(): array {
        return $this->constraints;
    }

    public function getReferenceName(IModel $definition): ?string {
        return $this->references[get_class($definition)] ?? null;
    }

    public function isPersisted(): bool {
        return $this->isPersisted;
    }

    public function isGenerated(): bool {
        return $this->isGenerated;
    }

    public function isRequired(): bool {
        foreach ($this->constraints as $c) {
            if ($c instanceof INotNullableConstraint) {
                return true;
            }
        }
        return false;
    }
}