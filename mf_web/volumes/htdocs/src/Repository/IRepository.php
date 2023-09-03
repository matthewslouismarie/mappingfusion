<?php

namespace MF\Repository;

use MF\Framework\DataStructure\AppObject;

interface IRepository
{
    public function find(string $id): ?AppObject;
}