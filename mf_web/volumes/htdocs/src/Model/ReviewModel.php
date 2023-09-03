<?php

namespace MF\Model;

use MF\Framework\Constraints\RangeConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\IntegerModel;
use MF\Framework\Model\SlugModel;
use MF\Framework\Model\StringModel;
use MF\Framework\Model\UintModel;

class ReviewModel extends AbstractEntity
{
    public function __construct(
        private ?PlayableModel $playableModel = null,
        private ?ArticleModel $articleModel = null,
    ) {
    }

    public function getArrayDefinition(): array {
        $properties = [
            'id' => new UintModel(isNullable: true),
            'article_id' => new SlugModel(),
            'playable_id' => new SlugModel(),
            'rating' => new IntegerModel([new RangeConstraint(min: 1, max: 5)]),
            'body' => new StringModel([]),
            'cons' => new StringModel([]),
            'pros' => new StringModel([]),
        ];
        if (null !== $this->playableModel) {
            $properties['playable'] = $this->playableModel;
        }
        if (null !== $this->articleModel) {
            $properties['article'] = $this->articleModel;
        }
        return $properties;
    }
}