<?php

namespace MF\Framework\Constraints;

use InvalidArgumentException;
use MF\Framework\Model\IModel;

class EntityConstraint implements IConstraint
{
    /**
     * @param \MF\Framework\Model\IModel
     */
    public function __construct(
        private array $properties,
    ) {
        foreach ($properties as $key => $property) {
            if (!is_string($key) || !($property instanceof IModel)) {
                throw new InvalidArgumentException();
            }
        }
    }

    /**
     * @return \MF\Framework\Model\IModel[] An array of properties, indexed by property name.
     */
    public function getProperties(): array {
        return $this->properties;
    }
}