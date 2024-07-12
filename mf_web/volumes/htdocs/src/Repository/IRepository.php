<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

interface IRepository
{
    /**
     * @return string The ID of the newly-inserted row.
     */
    public function add(AppObject $entity): string;

    public function delete(string $id): void;

    public function find(string $id): ?AppObject;

    public function update(AppObject $entity, string $previousId): void;
}