<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;

class ReviewModelFactory
{
    const RATING_LOWEST = 1;

    const RATING_MAX = 5;

    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(
        ?EntityModel $playableModel = null,
        ?EntityModel $articleModel = null,
        bool $isNullable = false,
    ): EntityModel {
        $properties = [
            'id' => new IntModel(0, IntModel::MAX_UNSIGNED, true),
            'article_id' => $this->slugModelFactory->getSlugModel(),
            'playable_id' => $this->slugModelFactory->getSlugModel(),
            'rating' => new IntModel(self::RATING_LOWEST, self::RATING_MAX),
            'body' => new StringModel(),
            'cons' => new StringModel(),
            'pros' => new StringModel(),
        ];
        if (null !== $playableModel) {
            $properties['playable'] = $playableModel;
        }
        if (null !== $articleModel) {
            $properties['article'] = $articleModel;
        }

        return new EntityModel(
            'review',
            $properties,
            'id',
            $isNullable,
        );
    }
}