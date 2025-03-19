<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\StringModel;

class MemberModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(?EntityModel $authorModel = null, bool $isNullable = false): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'password' => new StringModel(),
            'uuid' => new StringModel(),
            'author_id' => $this->slugModelFactory->getSlugModel(true),
        ];

        if (null !== $authorModel) {
            $properties['author'] = $authorModel;
        }
        
        return new EntityModel(
            'member',
            $properties,
            'id',
            $isNullable,
        );
    }
}