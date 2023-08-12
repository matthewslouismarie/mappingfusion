<?php

namespace MF\Model;

use MF\Constraint\BoolConstraint;
use MF\Constraint\FileConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;
use MF\Constraint\TextConstraint;


/**
 * @todo Create factory for article entities?
 */
class ArticleModel implements IModel
{
    public function __construct(
        private ?ReviewModel $reviewModel = null,
    ) {
    }

    public function getProperties(): array {
        $properties = [
            new ModelProperty(
                'id',
                new SlugConstraint(),
                isGenerated: true,
            ),
            new ModelProperty('author_id', new SlugConstraint(), isGenerated: true),
            new ModelProperty('category_id', new SlugConstraint),
            new ModelProperty('body', new TextConstraint()),
            new ModelProperty('is_featured', new BoolConstraint()),
            new ModelProperty('title', new LongStringConstraint()),
            new ModelProperty('sub_title', new LongStringConstraint(), isRequired: false),
            new ModelProperty('cover_filename', new FileConstraint()),
            new ModelProperty('creation_date_time', new class implements IDateTimeConstraint {}, isGenerated: true),
            new ModelProperty('last_update_date_time', new class implements IDateTimeConstraint {}, isGenerated: true),
            new ModelProperty('review', new ReviewModel(), isRequired: false),
            new ModelProperty('category', new CategoryModel(), isRequired: false),
        ];
        return $properties;
    }
}