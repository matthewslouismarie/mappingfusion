<?php

namespace MF\Model;

use MF\Constraint\EnumConstraint;
use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;
use MF\Constraint\TextConstraint;
use MF\Constraint\UintConstraint;
use MF\Enum\LinkType;
use MF\Model\ModelProperty;

/**
 * @todo Custom constraint for type and url.
 */
class PlayableLinkModel implements IModel
{
    public function __construct(
        private string $name = 'link',
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', new UintConstraint(), isGenerated: true, isRequired: false),
            new ModelProperty('playable_id', new SlugConstraint()),
            new ModelProperty('name', new LongStringConstraint()),
            new ModelProperty('type', new EnumConstraint(LinkType::cases())),
            new ModelProperty('url', new LongStringConstraint()),
        ];
    }
}