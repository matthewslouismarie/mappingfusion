<?php

namespace MF\Database;

use ArrayAccess;
use DateTimeImmutable;
use DI\Container;
use MF\DataStructure\AppObject;
use MF\Enum\ModelPropertyType;;
use MF\Model\IModelDefinition;
use MF\Exception\InvalidEntityArrayException;

class DbEntityManager
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function toAppObject(
        array $entity,
        IModelDefinition $definition,
        string $prefix = '',
        ?IModelDefinition $origin = null,
        array $childrenToProcess = [],
    ): ArrayAccess {
        $data = [];
        foreach ($definition->getProperties() as $p) {
            $pName = $prefix . $p->getName();
            $pParentName = null !== $origin && null !== $p->getReferenceName($origin) ? $origin->getName() . '_' . $p->getReferenceName($origin): null;
            $dbArrayKey = $pParentName ?? $pName;
            if (!array_key_exists($dbArrayKey, $entity)) {
                throw new InvalidEntityArrayException(get_class($definition), property: $pName);
            }
            if (ModelPropertyType::BOOL === $p->getType()) {
                $data[$p->getName()] = 1 === $entity[$dbArrayKey] ? true : false;
            } elseif (ModelPropertyType::DATETIME === $p->getType()) {
                $data[$p->getName()] = new DateTimeImmutable($entity[$dbArrayKey]);
            } else {
                $data[$p->getName()] = $entity[$dbArrayKey];
            }
        }
        foreach ($childrenToProcess as $childId => $childDef) {
            $data[$childId] = $this->toAppObject($entity, $childDef, $childDef->getName() . '_', $definition);
        }
        return new AppObject($data);
    }

    public function toDbArray(AppObject $appObject, IModelDefinition $def, string $prefix = ''): array {
        $obObject = [];
        foreach ($def->getProperties() as $p) {
            if (ModelPropertyType::BOOL === $p->getType()) {
                $obObject[$prefix . $p->getName()] = 1 === $appObject[$p->getName()] ? 1 : 0;
            } else {
                $obObject[$prefix . $p->getName()] = $appObject[$p->getName()];
            }
        }
        return $obObject;
    }
}