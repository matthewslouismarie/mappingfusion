<?php

namespace MF\Form;

use InvalidArgumentException;
use MF\DataStructure\AppObject;
use MF\Model\ModelDefinition;

class FormObjectManager
{
    /**
     * @return mixed[] An app array created from the given definition, and populated with corresponding values from the
     * form object. For any particular property, if no corresponding value is found in the given form object, an
     * exception is thrown.
     */
    public function toAppObject(
        array $data,
        ModelDefinition $def,
        string $prefix = '',
    ): AppObject {
        $appArray = [];
        foreach ($def->getProperties() as $p) {
            $formValue = $data[$prefix . $p->getName()];
            $appArray[$p->getName()] = $formValue;
        }
        return new AppObject($appArray);
    }
}