<?php

namespace MF\Model;

/**
 * An entity model defines all entities of its type. It is a definition entirely made of a list of properties, each
 * having a name that MUST be unique. Each property has an assigned type and other attributes that define how it is to
 * be saved in the database, or converted and validated after a form submission.
 */
interface ModelDefinition
{
    public function getName(): string;

    /**
     * @return ModelProperty[] An array of model properties defining the model.
     */
    public function getProperties(): array;

    public function getStoredData(): array;
}