<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
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

    public function create(?EntityModel $indexModel = null, ?EntityModel $bookModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'book_id' => $this->slugModelFactory->getSlugModel(),
            'title' => new StringModel(),
            'order' => new IntModel(min: 0, max: DatabaseManager::TINYINT_UNSIGNED_MAX),
        ];
        if (null !== $indexModel) {
            $properties['articles'] = new EntityListModel(
                new ForeignEntityModel($indexModel, 'chapter_id', 'id'),
            );
        }
        if (null !== $bookModel) {
            $properties['book'] = new ForeignEntityModel($bookModel, 'id', 'book_id');
        }
        
        return new EntityModel(
            'chapter',
            $properties,
            'id',
        );
    }
}