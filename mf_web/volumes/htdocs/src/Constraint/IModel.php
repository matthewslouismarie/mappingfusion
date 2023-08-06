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
     * @return string An identifier for the model. This can be used entities convert it back and from various forms.
     * @todo Remove?
     */
    public function getName(): string;

    /**
     * @return IModelProperty[] An array of model properties defining the model.
     */
    public function getProperties(): array;
}