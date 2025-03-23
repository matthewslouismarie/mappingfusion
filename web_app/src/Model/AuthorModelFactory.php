<?php

namespace MF\Model;

use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UploadedImageModelFactory;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\StringModel;

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

    public function create(?EntityModel $accountModel = null): EntityModel
    {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'name' => new StringModel(lowerLimit: 1),
            'avatar_filename' => $this->uploadedImageModelFactory->getModel(isNullable: true),
        ];
        
        if (null !== $accountModel) {
            $properties['account'] = new ForeignEntityModel($accountModel, 'author_id', 'id', true);
        }
        
        return new EntityModel(
            'author',
            $properties,
        );
    }
}