<?php

namespace MF\Model;

use MF\Constraint\BoolConstraint;
use MF\Constraint\IModel;
use MF\Constraint\SlugConstraint;
use MF\Constraint\TextConstraint;
use MF\Constraint\UintConstraint;

class ContributionModel implements IModel
{
    public function getName(): string {
        return 'contribution';
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', new UintConstraint(), isGenerated: true, isRequired: false),
            new ModelProperty('author_id', new SlugConstraint()),
            new ModelProperty('playable_id', new SlugConstraint()),
            new ModelProperty('is_author', new BoolConstraint()),
            new ModelProperty('summary', new TextConstraint(), isRequired: false),
        ];
    }
}