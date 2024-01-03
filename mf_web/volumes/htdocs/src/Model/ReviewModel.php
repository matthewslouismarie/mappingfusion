<?php

namespace MF\Model;

use LM\WebFramework\Constraints\RangeConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IntegerModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use LM\WebFramework\Model\UintModel;

class ReviewModel extends AbstractEntity
{
    public function __construct(
        private ?PlayableModel $playableModel = null,
        private ?ArticleModel $articleModel = null,
        private bool $isNullable = false,
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

    public function isNullable(): bool {
        return $this->isNullable;
    }
}