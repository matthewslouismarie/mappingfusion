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
     * Transform DB Data into App Data.
     *
     * @param mixed $dbData DB Data.
     * @param IType $type The Type of the DB Data.
     * @return mixed App Data.
     */
    public function toAppData(mixed $dbData, IType $type): mixed {
        if ($type instanceof IModel) {
            $appArray = [];
            foreach ($type->getProperties() as $p) {
                if ($p->getType() instanceof IModel) {
                    $appArray[$p->getName()] = $this->toAppData($dbData, $p->getType());
                } else {
                    $appArray[$p->getName()] = $this->toAppData(
                        $dbData[$type->getName() . '_' . $p->getName()],
                        $p->getType(),
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
        } elseif ($type instanceof IArrayConstraint) {
            $appDatas = [];
            if ($type->getElementType() instanceof IModel) {
                foreach ($dbData as $dbArray) {
                    $appDatas[] = $this->toAppData($dbArray, $type->getElementType());
                }
            } else {
                foreach ($dbData as $dbArray) {
                    $appDatas[] = $this->toAppData($dbArray, $type->getElementType());
                }
            }
            return $appDatas;
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