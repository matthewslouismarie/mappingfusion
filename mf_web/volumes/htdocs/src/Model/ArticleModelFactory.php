<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UploadedImageModelFactory;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\StringModel;

class ArticleModelFactory
{
    public function __construct(
        private SlugModelFactory $slugFactory,
        private UploadedImageModelFactory $uploadedImageModelFactory,
    ) {
    }

    public function create(
        ?EntityModel $authorModel = null,
        ?EntityModel $categoryModel = null,
        ?EntityModel $reviewModel = null,
        ?EntityModel $chapterIndexModel = null,
        bool $chapterId = false,
        bool $isNullable = false,
    ): EntityModel {
        $slugModel = $this->slugFactory->getSlugModel();
        $properties = [
            'id' => $slugModel,
            'author_id' => $slugModel,
            'category_id' => $slugModel,
            'body' => new StringModel(),
            'is_featured' => new BoolModel(),
            'is_published' => new BoolModel(),
            'title' => new StringModel(),
            'sub_title' => new StringModel(isNullable: true),
            'cover_filename' => $this->uploadedImageModelFactory->createModel(),
            'creation_date_time' => new DateTimeModel(),
            'last_update_date_time' => new DateTimeModel(),
            'thumbnail_filename' => $this->uploadedImageModelFactory->createModel(true),
        ];
        if ($chapterId) {
            $properties['chapter_id'] = $slugModel;
        }
        if (null !== $reviewModel) {
            $properties['review'] = $reviewModel;
        }
        if (null !== $categoryModel) {
            $properties['category'] = $categoryModel;
        }
        if (null !== $authorModel) {
            $properties['redactor'] = $authorModel;
        }
        if (null !== $chapterIndexModel) {
            $properties['chapter_index'] = $chapterIndexModel;
        }
        
        return new EntityModel(
            'article',
            $properties,
            'id',
            $isNullable,
        );
    }
}