<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UploadedImageModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\StringModel as TypeStringModel;

/**
 * @todo Add knowsAbout, memberOf
 * @todo Rename to Person
 */
class AuthorModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
        private UploadedImageModelFactory $uploadedImageModelFactory,
    ) {
    }

    public function create(?EntityModel $memberModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'name' => new TypeStringModel(lowerLimit: 1),
            'avatar_filename' => $this->uploadedImageModelFactory->createModel(true),
        ];
        if (null !== $memberModel) {
            $properties['member'] = $memberModel;
        }
        
        return new EntityModel(
            'author',
            $properties,
            'id',
        );
    }
}