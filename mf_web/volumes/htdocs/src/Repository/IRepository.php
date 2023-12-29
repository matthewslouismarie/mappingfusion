<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

interface IRepository
{
    public function find(string $id): ?AppObject;
}