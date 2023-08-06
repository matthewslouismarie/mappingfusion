<?php

namespace MF\Model;

use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;

class AuthorModel implements IModel
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
            new ModelProperty('id', new SlugConstraint()),
            new ModelProperty('name', new LongStringConstraint()),
        ];
    }
}