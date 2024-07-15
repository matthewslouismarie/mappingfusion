<?php

namespace MF\Model;

use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\UintModel;
use MF\Database\DatabaseManager;

class ChapterIndexModel extends AbstractEntity
{
    public function __construct(bool $isNullable = false, bool $isNew = false)
    {
        $properties = [
            'id' => new UintModel(max: DatabaseManager::SMALLINT_UNSIGNED_MAX, isNullable: $isNew),
            'article_id' => new SlugModel(),
            'chapter_id' => new SlugModel(),
            'order' => new UintModel(max: DatabaseManager::TINYINT_UNSIGNED_MAX),
        ];

        parent::__construct($properties, $isNullable);
    }
}