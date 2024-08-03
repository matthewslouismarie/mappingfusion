<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use MF\Database\DatabaseManager;

class ChapterModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(?EntityModel $articleModel = null, ?EntityModel $bookModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'book_id' => $this->slugModelFactory->getSlugModel(),
            'title' => new StringModel(),
            'order' => new IntModel(min: 0, max: DatabaseManager::TINYINT_UNSIGNED_MAX),
        ];
        if (null !== $articleModel) {
            $properties['articles'] = new ListModel($articleModel);
        }
        if (null !== $bookModel) {
            $properties['book'] = $bookModel;
        }
        
        return new EntityModel(
            'chapter',
            $properties,
            'id',
        );
    }
}