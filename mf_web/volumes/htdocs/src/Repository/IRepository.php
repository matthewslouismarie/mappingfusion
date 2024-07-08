<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

interface IRepository
{
    public function add(AppObject $entity): void;
    public function find(string $id): ?AppObject;
    public function update(AppObject $entity, string $previousId): void;
}