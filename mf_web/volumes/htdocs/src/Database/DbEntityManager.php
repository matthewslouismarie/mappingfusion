<?php

namespace MF\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\Constraint\IType;
use MF\DataStructure\AppObject;
use MF\Exception\Database\InvalidDbData;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory.
 */
class DbEntityManager
{
    const SEP = '_';

    private function isOrdered(array $array): bool {
        return count($array) === count(array_filter($array, fn($key) => is_int($key), ARRAY_FILTER_USE_KEY));
    }


    /**
     * Transform the given DB Array key and value into a Model’s Property.
     *
     * @param string $dbArrayKey The DB Array key.
     * @param mixed $dbValue The associated DB Data.
     * @param IModel $model The App Object Model to use to extract the property hierarchy.
     * @return mixed[] A recursive array using valid Property names for keys and terminating with the transformed DB
     * data.
     */
    public function toModelProperty(string $dbArrayKey, mixed $dbValue, IModel $model): ?array {
        foreach ($model->getProperties() as $p) {
            if ($p->getType() instanceof IModel) {
                $pKeys = $this->toModelProperty($dbArrayKey, $dbValue, $p->getType());
                if (null !== $pKeys) {
                    return [$p->getName() => $pKeys];
                }
            } else {
                if ($model->getName() . self::SEP . $p->getName() ===  $dbArrayKey) {
                    return [$p->getName() => $this->toAppData($dbValue, $p->getType())];
                }
            }
        }

        return null;
    }

    /**
     * Transform the given DB Array into an App Object using the specified Model.
     * 
     * @param mixed[] $dbArray A DB Array.
     * @param IModel $model A model.
     * @return AppObject A DB Array created from the Model and the DB Array.
     * @throws InvalidArgumentException If $dbArray is not a valid DB Array.
     */
    public function toAppObject(
        array $dbArray,
        IModel $model,
    ): AppObject {
        $appArray = [];

        foreach ($dbArray as $key => $value) {
            if (!is_scalar($value) && null !== $value) {
                throw new InvalidArgumentException("DB arrays must not hold non-scalar values: ");
            }

            $hierarchy = $this->toModelProperty($key, $value, $model);
            if (null !== $hierarchy) {
                $appArray = array_merge_recursive($appArray, $hierarchy);
            }
        }

        return new AppObject($appArray);
    }

    /**
     * Transform DB Data into App Data.
     *
     * @param mixed $dbData DB Data.
     * @param IType $type The Type of the DB Data.
     * @return mixed App Data.
     */
    public function toAppData(mixed $dbData, IType $type): mixed {
        if (null === $dbData) {
            return null;
        } elseif ($type instanceof IModel) {
            return $this->toAppObject($dbData, $type);
        } elseif ($type instanceof IArrayConstraint && $type->getElementType() instanceof IModel) {
            $appObjects = [];
            foreach ($dbData as $dbArray) {
                $appObjects[] = $this->toAppObject($dbArray, $type->getElementType());
            }
            return $appObjects;
        } elseif ($type instanceof IDateTimeConstraint) {
            return new DateTimeImmutable($dbData);
        } elseif ($type instanceof IBooleanConstraint) {
            return in_array($dbData, [0, 1], true) ? 1 === $dbData : throw new InvalidDbData();
        } else {
            return $dbData;
        }
    }

    /**
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     */
    public function toDbValue(mixed $appData, string $prefix = ''): mixed {
        if ($appData instanceof AppObject) {
            return $this->toDbValue($appData->toArray(), $prefix);
        } elseif (is_bool($appData)) {
            return $appData ? 1 : 0;
        } elseif ($appData instanceof DateTimeImmutable) {
            return $appData->format('Y-m-d H:i:s');
        } elseif (is_array($appData)) {
            $dbArray = [];
            if ($this->isOrdered($appData)) {
                $i = 0;
                foreach ($appData as $subValue) {
                    $dbArray[$prefix . ++$i] = $this->toDbValue($subValue, $prefix);
                }
            } else {
                foreach ($appData as $key => $subValue) {
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
            return $appData;
        }
    }
}