<?php

namespace MF\Constraint;

/**
 * Defines a class of entities.
 * 
 * A model is a definition made of a list of properties (IModelProperty), each having a name that MUST be unique.
 */
interface IModel extends IType
{
    /**
     * @return \MF\Model\IModelProperty[] An array of model properties defining the model.
     */
    public function getProperties(): array;
}