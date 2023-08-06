<?php

namespace MF\Model;
use MF\Enum\ModelPropertyType;

interface IModelProperty
{
    /**
     * @return string An identifier for the property. It MUST be unique among other properties of the same model.
     */
    public function getName(): string;

    /**
     * Hint that this property is generated automatically (i.e. by the app or the RDBMS), and that it should not be the
     * responsability of the user to provide a value for this property.
     * 
     * @return bool True if the property is generated automatically, false otherwise.
     */
    public function isGenerated(): bool;

    /**
     * @return bool Whether or not this property requires a value.
     */
    public function isRequired(): bool;

    /**
     * @return string[] An array of constraint identifiers, used to validate the entity (e.g. after a form submission or
     * before a database create or update operation).
     */
    public function getConstraints(): array;

    /**
     * @return ?string The name of the property in the given model that references this property, or null if
     * the given model .
     */
    public function getReferenceName(IModelDefinition $definition): ?string;

    /**
     * @return ModelPropertyType An identifier for the property value type. This provides information as to how create a
     * form from the model definition, retrieve an entity from a request, or how to convert the entity to and from
     * database data.
     * @todo Instead of a modelpropertytype, make it return an object that for instance, defines the maximum length, the
     * accepted values, etc.
     */
    public function getType(): ModelPropertyType;
}