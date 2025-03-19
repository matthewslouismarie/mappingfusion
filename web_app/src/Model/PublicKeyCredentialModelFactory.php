<?php

namespace MF\Model;

use LM\WebFramework\Model\Type\DataArrayModel;
use LM\WebFramework\Model\Type\IModel;
use LM\WebFramework\Model\Type\JsonModel;

class PublicKeyCredentialModelFactory
{
    public function create(): IModel
    {
        return new DataArrayModel(
            [
                'public-key-credential' => new JsonModel(),
            ],
        );
    }
}