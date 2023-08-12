<?php

namespace MF\Model;

use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;
use MF\Constraint\TextConstraint;
use MF\Model\ModelProperty;

class MemberModel implements IModel
{
    public function __construct(
        private string $name = 'member',
    ) {
    }

    public function getProperties(): array {
        return [
            new ModelProperty('id', new SlugConstraint()),
            new ModelProperty('password', new LongStringConstraint(), isGenerated: true),
        ];
    }
}