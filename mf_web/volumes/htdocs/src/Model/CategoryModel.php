<?php

namespace MF\Model;

use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\SlugModel;
use MF\Framework\Model\StringModel;

class CategoryModel extends AbstractEntity
{
    public function __construct(
        ?CategoryModel $parentCategory = null,
        bool $isNullable = false,
    ) {
        $properties = [
            'id' => new SlugModel(),
            'name' => new StringModel(),
            'parent_id' => new SlugModel(isNullable: true),
        ];
        if (null !== $parentCategory) {
            $properties['parent'] = $parentCategory;
        }
        parent::__construct($properties, $isNullable);
    }
}