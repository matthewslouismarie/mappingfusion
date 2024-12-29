<?php

namespace MF\Model;

use LM\WebFramework\Model\Constraints\EnumConstraint;
use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UrlModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\IntModel;
use LM\WebFramework\Model\Type\StringModel;
use MF\Enum\LinkType;

/**
 * @todo Custom constraint for type and url.
 */
class PlayableLinkModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
        private UrlModelFactory $urlModelFactory,
    ) {
    }

    public function create(): EntityModel {
        return new EntityModel(
            'link',
            [
                'id' => new IntModel(isNullable: true),
                'playable_id' => $this->slugModelFactory->getSlugModel(),
                'name' => new StringModel(),
                'type' => new StringModel(enumConstraint: new EnumConstraint(LinkType::cases())),
                'url' => $this->urlModelFactory->getUrlModel(),
            ],
            'id',
        );
    }
}