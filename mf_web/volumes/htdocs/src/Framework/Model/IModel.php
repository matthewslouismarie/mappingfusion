<?php

namespace MF\Framework\Model;

interface IModel
{
    public function isBool(): bool;

    public function isNullable(): bool;

    /**
     * @return \MF\Framework\Model\IModel[] An indexed array of models.
     */
    public function getArrayDefinition(): ?array;

    /**
     * @return \MF\Framework\Constraints\IDateTimeConstraint[]
     */
    public function getDateTimeConstraints(): ?array;

    /**
     * @return \MF\Framework\Constraints\INumberConstraint[]
     */
    public function getIntegerConstraints(): ?array;

    public function getListNodeModel(): ?IModel;

    /**
     * @return \MF\Framework\Constraints\IStringConstraint[] A list of string constraints.
     */
    public function getStringConstraints(): ?array;
}