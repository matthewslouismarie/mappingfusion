<?php

namespace MF\Model;

use MF\Constraint\IModel;
use MF\Enum\ModelPropertyType;

class ReviewModel implements IModel
{
    public function getName(): string {
        return 'review';
    }

    public function getProperties(): array {
        return [
            new ModelProperty(
                'id',
                ModelPropertyType::UINT,
                true,
            ),
            new ModelProperty(
                'article_id',
                ModelPropertyType::VARCHAR,
                alternateLocations: [ArticleModel::class => 'id'],
            ),
            new ModelProperty('playable_id', ModelPropertyType::VARCHAR),
            new ModelProperty('rating', Rating::class),
            new ModelProperty('body', ModelPropertyType::TEXT),
            new ModelProperty('cons', ModelPropertyType::TEXT),
            new ModelProperty('pros', ModelPropertyType::TEXT),
        ];
    }
}