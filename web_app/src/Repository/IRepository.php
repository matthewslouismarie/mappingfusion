<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppObject;

/**
 * Serves to abstract the entity persistence mechanism.
 * 
 * Regardless of the persistence mechanism used (i.e. the mechanism for storing
 * and retrieving app data), the rest of the application can use implementations
 * of this class to find, update and delete entities from the app data.
 */
interface IRepository
{
    /**
     * Persist a new entity.
     * 
     * @param  AppObject $entity The entity to be persisted.
     * @return string The ID of the newly-inserted row.
     */
    public function add(AppObject $entity): string;

    /**
     * Delete an already-persisted entity.
     * 
     * @param string $id The ID of the entity to delete.
     */
    public function delete(string $id): void;

    /**
     * Retrieve an already persisted entity.
     * 
     * @param string $id The ID of the entity to retrieve.
     * @todo  Return AppObject or throw exception.
     */
    public function find(string $id): ?AppObject;
}