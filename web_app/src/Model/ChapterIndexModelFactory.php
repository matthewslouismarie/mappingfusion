<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IntModel;
use MF\Database\DatabaseManager;

class ChapterIndexModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(bool $isNullable = false, bool $isNew = false): EntityModel
    {
        $properties = [
            'id' => new IntModel(
                min: 0,
                max: DatabaseManager::SMALLINT_UNSIGNED_MAX,
                isNullable: $isNew,
            ),
            'article_id' => $this->slugModelFactory->getSlugModel(),
            'chapter_id' => $this->slugModelFactory->getSlugModel(),
            'order' => new IntModel(min: 0, max: DatabaseManager::TINYINT_UNSIGNED_MAX),
        ];
        
        return new EntityModel(
            'chapter_index',
            $properties,
            'id',
            $isNullable,
        );
    }
}