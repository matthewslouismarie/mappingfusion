<?php

namespace MF\DataStructure;

use MF\Constraint\IModel;
use MF\Model\IModelProperty;

class AppObjectFactory
{
    /**
     * @todo Validate input app array from a given definition.
     */
    public function create(array $data, IModel $model, string $prefix = ''): AppObject {
        $newData = [];
        foreach ($model->getProperties() as $p) {
            $value = $data[$prefix . $p->getName()] ?? null;
            $newData[$p->getName()] = $this->getValue($p, $value);
        }
        return new AppObject($newData);
    }

    public function getValue(IModelProperty $property, mixed $data): mixed {
        if (null === $data) {
            return null;
        } elseif ($property->getType() instanceof IModel) {
            return $this->create($data, $property->getType());
        } elseif ($property->getType() instanceof IArrayConstraint && $property->getType()->getElementType() instanceof IModel) {
            $newData = [];
            foreach ($data as $element) {
                $newData[] = $this->create($element, $property->getType()->getElementType());
            }
            return $newData;
        } elseif ($property->getType() instanceof IDateTimeConstraint) {
            return new DateTimeImmutable($data);
        } else {
            return $data;
        }
    }
}