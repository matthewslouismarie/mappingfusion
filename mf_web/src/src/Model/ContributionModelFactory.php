<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\BoolModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;

class ContributionModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(?EntityModel $authorModel = null): EntityModel
    {
        $properties = [
            'id' => new IntModel(min: 0, isNullable: true),
            'author_id' => $this->slugModelFactory->getSlugModel(),
            'playable_id' => $this->slugModelFactory->getSlugModel(),
            'is_author' => new BoolModel(),
            'summary' => new StringModel(isNullable: true),
        ];

        if (null !== $authorModel) {
            $properties['author'] = new ForeignEntityModel(
                $authorModel,
                'id',
                'author_id'
            );
        }
        
        return new EntityModel(
            'contribution',
            $properties,
            'id',
        );
    }
}