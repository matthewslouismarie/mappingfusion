<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\StringModel;

class CategoryModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(?EntityModel $parentCategory = null, bool $isNullable = false): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'name' => new StringModel(),
            'parent_id' => new StringModel(isNullable: true),
        ];
        if (null !== $parentCategory) {
            $properties['parent'] = $parentCategory;
        }
        
        return new EntityModel(
            'category',
            $properties,
            'id',
            $isNullable,
        );
    }
}