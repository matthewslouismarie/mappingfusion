<?php

namespace MF\Model;

use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\SlugModel;
use MF\Framework\Model\StringModel;

class CategoryModel extends AbstractEntity
{
    public function __construct() {
        parent::__construct([
            'id' => new SlugModel(),
            'name' => new StringModel(),
        ]);
    }
}