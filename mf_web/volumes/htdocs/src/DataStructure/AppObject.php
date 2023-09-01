<?php

namespace MF\DataStructure;

use ArrayAccess;
use BadMethodCallException;
use MF\Model\KeyName;

/**
 * Immutable array that whose values can be accessed like an object property.
 */
class AppObject implements ArrayAccess
{
    private array $data;

    /**
     * @param mixed[] $appArray An app array.
     * @todo Add back validation?
     */
    public function __construct(array $appArray) {
        $this->data = $appArray;
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
        return new self([$offet => $value] + $this->data);
    }

    public function toArray(): array {
        $appArray = [];
        foreach ($this->data as $pName => $pValue) {
            $appArray[$pName] = $pValue instanceof self ? $pValue->toArray() : $pValue;
        }
        return $appArray;
    }

    public function isEqualTo(mixed $appObject): bool {
        if (!($appObject instanceof AppObject)) {
            return false;
        }
        foreach ($this->data as $key => $value) {
            $isEqual = null;
            if ($value instanceof AppObject) {
                $isEqual = $value->isEqualTo($appObject[$key]);
            } elseif (gettype($value) === 'object') {
                $isEqual = $value == $appObject[$key];
            } else {
                $isEqual = $value === $appObject[$key];
            }
            if (!$isEqual) {
                var_dump($value, $appObject[$key]);
                return false;
            }
        }
        return true;
    }
}