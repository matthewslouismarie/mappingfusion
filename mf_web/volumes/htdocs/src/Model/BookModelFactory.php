<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel as TypeStringModel;

class BookModelFactory
{
    public function __construct(
        private ArticleModelFactory $articleModelFactory,
        private SlugModelFactory $slugModelFactory,
        private ChapterModelFactory $chapterModelFactory,
    ) {
    }

    public function create(?EntityModel $chapterModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'title' => new TypeStringModel(),
        ];
        if (null !== $chapterModel) {
            $properties['chapters'] = new ListModel($chapterModel);
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
        return $this->create($this->chapterModelFactory->create($articleModel));
    }
}