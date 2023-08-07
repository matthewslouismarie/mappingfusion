<?php

namespace MF\DataStructure;

use ArrayAccess;
use BadMethodCallException;
use DateTimeImmutable;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\Model\KeyName;

/**
 * @todo Validate input app array from a given definition.
 */
class AppObject implements ArrayAccess
{
    private array $data;

    private IModel $model;

    /**
     * Treats missing properties and present properties with null values indifferently.
     * @param mixed[] $scalarArray A scalar array.
     * @param IModel $model The entity model.
     * @todo Add back validation?
     */
    public function __construct(array $scalarArray, IModel $model) {
        $this->data = [];
        foreach ($model->getProperties() as $p) {
            $scalarValue = $scalarArray[$p->getName()] ?? null;
            if (null === $scalarValue) {
                $this->data[$p->getName()] = $scalarValue;
            } elseif ($p->getType() instanceof IModel) {
                $this->data[$p->getName()] = new self($scalarValue, $p->getType());
            } elseif ($p->getType() instanceof IArrayConstraint && $p->getType()->getElementType() instanceof IModel) {
                $this->data[$p->getName()] = [];
                foreach ($scalarValue as $element) {
                    $this->data[$p->getName()][] = new self($element, $p->getType()->getElementType());
                }
            } elseif ($p->getType() instanceof IDateTimeConstraint) {
                $this->data[$p->getName()] = new DateTimeImmutable($scalarValue);
            } else {
                $this->data[$p->getName()] = $scalarValue;
            }
        }

    }

    public function __get(string $name): mixed {
        return $this->attributeGet($name);
    }

    public function attributeGet(string $offset): mixed {
        $keyName = (new KeyName($offset))->__toString();
        return $this->data[$keyName];
    }

    public function offsetExists(mixed $offset): bool {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet(mixed $offset): mixed {
        return isset($this->data[$offset]) ? $this->data[$offset] : $this->attributeGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void {
        throw new BadMethodCallException('This object cannot be modified.');
    }

    public function offsetUnset(mixed $offset): void {
        throw new BadMethodCallException('This object cannot be modified.');
    }

    public function set(string $offet, mixed $value): self {
        return new self([$offet => $value] + $this->data, $this->model);
    }
}