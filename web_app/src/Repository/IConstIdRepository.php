<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

/**
 * Repository for models that do not allow editing the ID of persisted entities.
 */
interface IConstIdRepository extends IRepository
{
    /**
     * Updates an already persisted entity, without modifying its ID.
     * 
     * @param AppObject $entity The updated entity to persist.
     */
    public function update(AppObject $entity): void;
}