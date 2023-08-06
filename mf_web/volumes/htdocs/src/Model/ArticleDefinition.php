<?php

namespace MF\Model;

use MF\Enum\ModelPropertyType;


class ArticleDefinition implements IModelDefinition
{
    public function getName(): string {
        return 'article';
    }

    public function getProperties(): array {
        return [
            new ModelProperty(
                'id',
                ModelPropertyType::VARCHAR,
                isGenerated: true,
            ),
            new ModelProperty('author_id', ModelPropertyType::VARCHAR, isGenerated: true),
            new ModelProperty('category_id', ModelPropertyType::VARCHAR),
            new ModelProperty('body', ModelPropertyType::TEXT),
            new ModelProperty('is_featured', ModelPropertyType::BOOL),
            new ModelProperty('title', ModelPropertyType::VARCHAR),
            new ModelProperty('sub_title', ModelPropertyType::VARCHAR, isRequired: false),
            new ModelProperty('cover_filename', ModelPropertyType::IMAGE),
            new ModelProperty('creation_date_time', ModelPropertyType::DATETIME, isGenerated: true),
            new ModelProperty('last_update_date_time', ModelPropertyType::DATETIME, isGenerated: true),
        ];
    }
}