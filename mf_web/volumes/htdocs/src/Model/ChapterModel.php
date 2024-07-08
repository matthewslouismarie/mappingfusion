<?php

namespace MF\Model;

use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;

class ChapterModel extends AbstractEntity
{
    public function __construct(
        ?IModel $articleModel = null,
    ) {
        $properties = [
            'id' => new SlugModel(),
            'book_id' => new SlugModel(),
            'title' => new StringModel(),
        ];
        if (null !== $articleModel) {
            $properties['articles'] = new ListModel($articleModel);
        }
        parent::__construct($properties);
    }
}