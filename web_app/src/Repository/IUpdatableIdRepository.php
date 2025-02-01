<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

/**
 * Repository for models that allow editing the ID of persisted entities.
 */
interface IUpdatableIdRepository extends IRepository
{
    /**
     * Updates an already persisted entity, without modifying its ID.
     * 
     * @param AppObject $entity      The updated entity to persist.
     * @param string    $persistedId The currently persisted ID of the entity to update.
     */
    public function update(AppObject $entity, string $persistedId): void;
}