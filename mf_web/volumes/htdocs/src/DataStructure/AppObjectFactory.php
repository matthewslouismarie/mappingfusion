<?php

namespace MF\DataStructure;

use DateTimeImmutable;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\Constraint\IType;

class AppObjectFactory
{
    /**
     * @todo Validate input app array from a given definition.
     */
    public function create(array $data, IModel $model, string $prefix = ''): AppObject {
        $newData = [];
        foreach ($model->getProperties() as $p) {
            $value = $data[$prefix . $p->getName()] ?? null;
            $newData[$p->getName()] = $this->getValue($p->getType(), $value);
        }
        return new AppObject($newData);
    }

    public function getValue(IType $type, mixed $data): mixed {
        if (null === $data) {
            return null;
        } elseif ($type instanceof IModel) {
            return $this->create($data, $type);
        } elseif ($type instanceof IArrayConstraint && $type->getElementType() instanceof IModel) {
            $newData = [];
            foreach ($data as $element) {
                $newData[] = $this->create($element, $type->getElementType());
            }
            return new AppObject($newData);
        } else {
            return $data;
        }
    }
}