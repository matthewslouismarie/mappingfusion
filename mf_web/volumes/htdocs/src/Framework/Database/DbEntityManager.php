<?php

namespace MF\Framework\Database;

use DateTimeImmutable;
use InvalidArgumentException;
use MF\Framework\DataStructures\AppObject;
use MF\Framework\Model\IModel;
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
     * Transform DB Data into App Data.
     *
     * @param mixed $dbData DB Data.
     * @param IModel $model The model of the DB Data.
     * @param string|null $prefix If DB Data is an array, the prefix to use when extracting its properties.
     * @return mixed App Data.
     * @throws InvalidArgumentException If $dbData is not of any DB Data variable type.
     */
    public function toAppData(mixed $dbData, IModel $model, ?string $prefix = null): mixed {
        if (is_array($dbData)) {
            if (null !== $model->getArrayDefinition()) {
                $appArray = [];
                foreach ($model->getArrayDefinition() as $key => $property) {
                    if (null !== $property->getArrayDefinition()) {
                        $appArray[$key] = $this->toAppData($dbData, $property, $key);
                    } else {
                        $appArray[$key] = $this->toAppData(
                            $dbData[$prefix . '_' . $key],
                            $property,
                        );
                    }
                }
                if (count($appArray) === count(array_filter($appArray, fn ($value) => null === $value))) {
                    return null;
                } else {
                    return new AppObject($appArray);
                }
            }
        }
        if (is_numeric($dbData)) {
            if ($model->isBool() && in_array($dbData, [0, 1], true)) {
                return 1 === $dbData;
            } elseif (null !== $model->getIntegerConstraints()) {
                return intval($dbData);
            }
        }
        if (is_string($dbData)) {
            if (null !== $model->getDateTimeConstraints()) {
                return new DateTimeImmutable($dbData);
            }
            if (null !== $model->getStringConstraints()) {
                return $dbData;
            }
        }
        if (null === $dbData) {
            if ($model->isNullable()) {
                return null;
            }
        }

        throw new InvalidArgumentException('$dbData is not of any type supported by the model.');
    }

    /**
     * Ignores list (ordered arrays).
     *
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     * @throws InvalidArgumentException If appData is a list.
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
                throw new InvalidArgumentException('Not supported.');
            } else {
                foreach ($appData as $pName => $pValue) {
                    if (is_array($pValue)) {
                        if (!$this->isOrdered($pValue)) {
                            $dbArray += $this->toDbValue($pValue, $pName);
                        }
                    } else {
                        $dbArray[$prefix . $pName] = $this->toDbValue($pValue);
                    }
                }
            }
            return $dbArray;
        } else {
            return $appData;
        }
    }
}