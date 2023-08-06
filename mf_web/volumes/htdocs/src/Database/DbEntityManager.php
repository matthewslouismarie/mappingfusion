<?php

namespace MF\Database;

use DateTimeImmutable;
use DateTimeInterface;
use DI\Container;
use MF\Constraint\IArrayConstraint;
use MF\Constraint\IBooleanConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\DataStructure\AppObject;
use UnexpectedValueException;

/**
 * @todo Could be renamed to DbEntityFactory / DbArrayFactory. It would then become a class instatiable with a
 * particular definition, so that it is possible to further customize how an entity is supposed to be transformed into
 * a DB array, or the other way around.
 */
class DbEntityManager
{
    public function __construct(
        private Container $container,
    ) {
    }

    /**
     * @param mixed[] $dbArray An array of scalars extracted from the database, with no more than one level and prefixes to
     * identify the tables they were extracted from.
     * @param string[] $prefixes An array indexed by prefixes, holding as their value the key to group them by.
     * @return mixed[] A scalar array.
     */
    public function toScalarArray(array $dbArray, array $prefixes): array {
        $scalarArray = [];
        foreach ($prefixes as $prefix => $group) {
            foreach ($dbArray as $key => $value) {
                if (str_starts_with($key, $prefix)) {
                    $prepKeyName = substr($key, strlen($prefix));
                    if (null === $group) {
                        $scalarArray[$prepKeyName] = $value;
                    } else {
                        if (!key_exists($group, $scalarArray)) {
                            $scalarArray[$group] = [];
                        }
                        $scalarArray[$group][$prefix] = $value;
                    }
                } else {
                    $scalarArray[$key] = $value;
                }
            }
        }
        return $scalarArray;
    }

    public function toAppObject(
        array $dbArray,
        IModel $model,
        array $prefixes,
    ): AppObject {
        $scalarArray = $this->toScalarArray($dbArray, $prefixes);

        foreach ($model->getProperties() as $p) {
            $pName = $p->getName();
            if ($p->getType() instanceof IBooleanConstraint) {
                $data[$p->getName()] = 1 === $scalarArray[$pName] ? true : false;
            } elseif ($p->getType() instanceof IDateTimeConstraint) {
                $data[$p->getName()] = new DateTimeImmutable($scalarArray[$pName]);
            } elseif ($p->getType() instanceof IModel) {
                $data[$p->getName()] = $this->toAppObject($scalarArray[$pName], $p->getType(), $prefixes);
            } elseif ($p->getType() instanceof IArrayConstraint) {
                
            } else {
                $data[$p->getName()] = $scalarArray[$pName];
            }
        }
        return new AppObject($data, $model);
    }

    /**
     * @throws UnexpectedValueException If some of the properties are set to be persisted and are not scalar.
     */
    public function toDbArray(AppObject $appObject, IModel $model, string $prefix = ''): array {
        $dbArray = [];
        foreach ($model->getProperties() as $p) {
            if ($p->isPersisted()) {
                if ($p->getType() instanceof IBooleanConstraint) {
                    $dbArray[$prefix . $p->getName()] = $appObject[$p->getName()] ? 1 : 0;
                } elseif ($p->getType() instanceof IDateTimeConstraint) {
                    $dbArray[$prefix . $p->getName()] = $appObject[$p->getName()]->format('Y-m-d H:i:s');
                } elseif ($p->getType() instanceof IModel || $p->getType() instanceof IArrayConstraint) {
                    throw new UnexpectedValueException($p->getName() . " is set to be persisted and is not scalar.");
                } else {
                    $dbArray[$prefix . $p->getName()] = $appObject[$p->getName()];
                }
            }
        }
        return $dbArray;
    }
}