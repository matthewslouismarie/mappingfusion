<?php

namespace MF\Model;

use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;

class CategoryModel implements IModel
{
    public function getProperties(): array {
        return [
            new ModelProperty('id', new SlugConstraint()),
            new ModelProperty('name', new LongStringConstraint()),
        ];
    }
}