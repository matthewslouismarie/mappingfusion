<?php

namespace MF\Form;

use MF\Constraint\IModel;
use MF\DataStructure\AppObject;

class FormObjectManager
{
    /**
     * @return mixed[] An app array created from the given definition, and populated with corresponding values from the
     * form object. For any particular property, if no corresponding value is found in the given form object, an
     * exception is thrown.
     * @todo Make it return a scalar array.
     */
    public function toAppObject(
        array $scalarArray,
        IModel $def,
        string $prefix = '',
    ): AppObject {
        $appArray = [];
        foreach ($def->getProperties() as $p) {
            $formValue = $scalarArray[$prefix . $p->getName()];
            $appArray[$p->getName()] = $formValue;
        }
        return new AppObject($appArray, $def);
    }
}