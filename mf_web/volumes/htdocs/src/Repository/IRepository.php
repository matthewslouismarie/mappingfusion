<?php

namespace MF\Repository;

use MF\Framework\DataStructures\AppObject;

interface IRepository
{
    public function find(string $id): ?AppObject;
}