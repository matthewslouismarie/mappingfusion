<?php

namespace MF\Model;

use MF\Framework\Constraints\EntityConstraint;
use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\IModel;
use MF\Framework\Model\StdModel;
use MF\Framework\Type\ModelType;

class AuthorModelFactory
{
    public function create(): IModel {
        return new StdModel(
            ModelType::Entity,
            [
                new EntityConstraint([
                    'id' => new StdModel(ModelType::String, [
                        new StringConstraint(minLength: 1, regex: StringConstraint::REGEX_DASHES),
                    ]),
                    'name' => new StdModel(ModelType::String, [
                        new StringConstraint(minLength: 1),
                    ]),
                ])
            ],
        );
    }
}