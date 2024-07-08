<?php

namespace MF\Model;

use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;

class BookModel extends AbstractEntity
{
    public function __construct(
        ?ChapterModel $chapterModel = null,
    ) {
        $properties = [
            'id' => new SlugModel(),
            'title' => new StringModel(),
        ];
        if (null !== $chapterModel) {
            $properties['chapters'] = new ListModel($chapterModel);
        }
        parent::__construct($properties);
    }
}