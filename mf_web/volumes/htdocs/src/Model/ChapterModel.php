<?php

namespace MF\Model;

use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use LM\WebFramework\Model\UintModel;

class ChapterModel extends AbstractEntity
{
    public function __construct(
        ?IModel $articleModel = null,
        ?IModel $bookModel = null,
    ) {
        $properties = [
            'id' => new SlugModel(),
            'book_id' => new SlugModel(),
            'title' => new StringModel(),
            'order' => new UintModel(max: 255),
        ];
        if (null !== $articleModel) {
            $properties['articles'] = new ListModel($articleModel);
        }
        if (null !== $bookModel) {
            $properties['book'] = $bookModel;
        }
        parent::__construct($properties);
    }
}