<?php

namespace MF\Model;

interface EntityInterface
{
    /**
     * @return array An array containing the entity’s properties (as ASCII strings) and their respective values
     * (as scalars). All the properties MUST be present, they CAN have the null value.
     */
    public function toArray(): array;
}