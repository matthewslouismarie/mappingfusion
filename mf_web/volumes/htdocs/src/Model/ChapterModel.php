<?php

namespace MF\Model;

use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;

class ChapterModel extends AbstractEntity
{
    public function __construct(
    ) {
        $properties = [
            'id' => new SlugModel(),
            'book_id' => new SlugModel(),
            'title' => new StringModel(),
        ];
        parent::__construct($properties);
    }
}