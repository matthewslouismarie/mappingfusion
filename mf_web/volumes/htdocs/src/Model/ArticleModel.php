<?php

namespace MF\Model;

use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\BoolModel;
use LM\WebFramework\Model\DateTimeModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;

class ArticleModel extends AbstractEntity
{
    public function __construct(
        ?AuthorModel $authorModel = null,
        ?CategoryModel $categoryModel = null,
        ?ReviewModel $reviewModel = null,
        bool $chapterId = false,
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
        if ($chapterId) {
            $properties['chapter_id'] = new SlugModel(isNullable: true);
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
        parent::__construct($properties);
    }
}