<?php

namespace MF\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory. It would then become a class instatiable with a
 * particular definition, so that it is possible to further customize how an entity is supposed to be transformed into
 * a DB array, or the other way around.
 */
class DbEntityManager
{
    public function isOrdered(array $array): bool {
        return count($array) === count(array_filter($array, fn($key) => is_int($key), ARRAY_FILTER_USE_KEY));
    }

    public function getScalarProperty(string $property, mixed $value, array $groups, string $sep = '_'): array {
        $scalarProperties = [];

        // if (is_string($groups)) {
        //     if (str_starts_with($property, $groups . $sep)) {
        //         $scalarProperties = [
        //             $groups => [
        //                 substr($property, strlen($groups . $sep)) => $value,
        //             ],
        //         ];
        //     }
        // elseif ($this->isOrdered($prefix)) {
        //     foreach ($prefix as $d) {
        //         if (str_starts_with($property, $d)) {
        //             $scalarProperties = array_merge_recursive(
        //                 $scalarProperties,
        //                 $this->getScalarProperty($property, $value, $d, $sep));
        //         }
        //     }
        // } else {
            foreach ($groups as $group) {
                if (!is_array($group) &&!is_string($group)) {
                    throw new UnexpectedValueException();
                }
                $prefix = null;
                $destinations = null;
                if (is_string($group)) {
                    $prefix = $group;
                    $destinations = [$group];
                } else {
                    $prefix = $group[0];
                    $destinations = $group[1];
                }
                if (str_starts_with($property, $prefix . $sep)) {
                    $newProp = [
                        substr($property, strlen($prefix . $sep)) => $value,
                    ];

                    foreach (array_reverse($destinations) as $d) {
                        $newProp = [
                            $d => $newProp,
                        ];
                    }
                    
                    return array_merge_recursive($scalarProperties, $newProp);
                }
            }
        // }

        if (0 !== count($scalarProperties)) {
            return $scalarProperties;
        } else {
            return [$property => $value];
        }
    }

    /**
     * @param mixed[] $dbArray An DB array of scalars extracted from the database, with no more than one dimension.
     * @param array[] $prefixes A recursive array indexed by a prefix and its sub-prefix, if any.
     * @return mixed[] A multi-dimensional, scalar array.
     * @throws InvalidArgumentException 
     */
    public function toScalarArray(array $dbArray, ?string $removePrefix = null, array $groups = [], string $sep = '_'): array {
        $scalarArray = [];

        foreach ($dbArray as $key => $value) {
            if (!is_scalar($value) && null !== $value) {
                throw new InvalidArgumentException("DB arrays must not hold non-scalar values: ");
            }
            if (null !== $removePrefix) {
                $groups[] = [$removePrefix, []];
            }
            $newProperty = $this->getScalarProperty($key, $value, $groups);
            $scalarArray = array_merge_recursive($scalarArray, $newProperty);
        }

        // if (null !== $removePrefix) {
        //     foreach ($scalarArray as $key => $value) {
        //         if (str_starts_with($key, $removePrefix)) {
        //             $scalarArray[substr($key, strlen($removePrefix . $sep))] = $value;
        //             unset($scalarArray[$key]);
        //         }
        //     }
        // }

        return $scalarArray;
    }

    /**
     * @todo Accept AppObject as well? Would allow repos to accept directly AppObject-s, although we migt not want that
     * could force (if AppObject validation is enforced) having all the entity properties defined.
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     */
    public function toDbValue(mixed $scalar, string $prefix = ''): mixed {
        if (is_bool($scalar)) {
            return $scalar ? 1 : 0;
        } elseif ($scalar instanceof DateTimeImmutable) {
            return $scalar->format('Y-m-d H:i:s');
        } elseif (is_array($scalar)) {
            $dbArray = [];
            if ($this->isOrdered($scalar)) {
                $i = 0;
                foreach ($scalar as $subValue) {
                    $dbArray[$prefix . ++$i] = $this->toDbValue($subValue, $prefix);
                }
            } else {
                foreach ($scalar as $key => $subValue) {
                    $dbValue = $this->toDbValue($subValue);
                    if (is_array($subValue)) {
                        $dbArray += $dbValue;
                    } else {
                        $dbArray[$prefix . $key] = $dbValue;
                    }
                }
            }
            return $dbArray;
        } else {
            return $scalar;
        }
    }
}