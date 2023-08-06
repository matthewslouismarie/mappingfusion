<?php

namespace MF\Repository;

use MF\DataStructure\AppObject;

interface IRepository
{
    public function find(string $id): ?AppObject;
}