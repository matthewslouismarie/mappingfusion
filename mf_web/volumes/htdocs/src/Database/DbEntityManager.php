<?php

namespace MF\Database;

use DateTimeImmutable;
use DomainException;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\DataStructure\AppObject;
use MF\Exception\Database\InvalidDbData;
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
     * @return mixed App Data.
     */
    public function toAppData(mixed $dbData, IModel $model, ?string $name = null): mixed {
        if (null !== $model->getArrayDefinition()) {
            $appArray = [];
            foreach ($model->getArrayDefinition() as $key => $property) {
                if (null !== $property->getArrayDefinition()) {
                    $appArray[$key] = $this->toAppData($dbData, $property);
                } else {
                    $appArray[$key] = $this->toAppData(
                        $dbData[$name . '_' . $key],
                        $property,
                    );
                }
            }
            if (count($appArray) === count(array_filter($appArray, fn ($value) => null === $value))) {
                return null;
            } else {
                return new AppObject($appArray);
            }
        } elseif (null === $dbData) {
            return null;
        } elseif (null !== $model->getListNodeModel()) {
            throw new DomainException('Not supported yet.');
        } elseif (null !== $model->getDateTimeConstraints()) {
            return new DateTimeImmutable($dbData);
        } elseif ($model->isBool()) {
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
                foreach ($appData as $pName => $pValue) {
                    if (is_array($pValue)) {
                        $dbArray += $this->toDbValue($pValue, $pName);
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