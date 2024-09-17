<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\StringModel;

class BookModelFactory
{
    public function __construct(
        private ChapterIndexModelFactory $chapterIndexModelFactory,
        private ArticleModelFactory $articleModelFactory,
        private SlugModelFactory $slugModelFactory,
        private ChapterModelFactory $chapterModelFactory,
    ) {
    }

    public function create(?EntityModel $chapterModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'title' => new StringModel(),
        ];
        if (null !== $chapterModel) {
            $properties['chapters'] = new EntityListModel(
                new ForeignEntityModel($chapterModel, 'book_id', 'id'),
            );
        }
        
        return new EntityModel(
            'book',
            $properties,
            'id',
        );
    }

    public function createWithChapterModel(): EntityModel
    {
        $articleModel = $this->articleModelFactory
            ->create()
            ->prune(['id', 'title'])
        ;
        $chapterIndexModel = $this->chapterIndexModelFactory->create(isNew: false);
        return $this->create($this->chapterModelFactory->create($chapterIndexModel));
    }
}