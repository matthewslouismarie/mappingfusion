<?php

namespace MF\Model;

use MF\Framework\Constraints\IUploadedImageConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\BoolModel;
use MF\Framework\Model\DateTimeModel;
use MF\Framework\Model\SlugModel;
use MF\Framework\Model\StringModel;

class ArticleModel extends AbstractEntity
{
    public function __construct(
        ?CategoryModel $categoryModel = null,
        ?ReviewModel $reviewModel = null,
    ) {
        $properties = [
            'id' => new SlugModel(),
            'author_id' => new SlugModel(),
            'category_id' => new SlugModel(),
            'body' => new StringModel([]),
            'is_featured' => new BoolModel(),
            'is_published' => new BoolModel(),
            'title' => new StringModel(),
            'sub_title' => new StringModel(isNullable: true),
            'cover_filename' => new StringModel([new class implements IUploadedImageConstraint {}]),
            'creation_date_time' => new DateTimeModel(),
            'last_update_date_time' => new DateTimeModel(),
            'thumbnail_filename' => new StringModel([new class implements IUploadedImageConstraint {}], true),
        ];
        if (null !== $reviewModel) {
            $properties['review'] = $reviewModel;
        }
        if (null !== $categoryModel) {
            $properties['category'] = $categoryModel;
        }
        parent::__construct($properties);
    }
}