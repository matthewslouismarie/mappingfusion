<?php

namespace MF\Form;

use InvalidArgumentException;
use MF\Model\ModelDefinition;

class FormObjectManager
{
    /**
     * @return mixed[] An app array created from the given definition, and populated with corresponding values from the
     * form object. For any particular property, if no corresponding value is found in the given form object, it is
     * populated with the existing app array instead. If the existing app array is not set, the value is set to null, at
     * least before being transformed by the property’s transformer.
     * @todo Take a HtmlFormElement as parameter instead?
     */
    public function toAppArray(FormValue $formValues, ModelDefinition $def, ?array $existingAppArray = null, string $prefix = ''): array {
        $appArray = [];
        foreach ($def->getProperties() as $p) {
            $formValue = $formValues->getFormValue($prefix . $p->getName());
            if (null !== $formValue) {
                if (count($formValue->getErrors()) > 0) {
                    throw new InvalidArgumentException('The form data must be error-free to be converted to an app array.');
                }
                $appArray[$p->getName()] = $formValue->getValue();
            } else {
                $appArray[$p->getName()] = $existingAppArray[$p->getName()] ?? null;
            }
        }
        foreach ($def->getProperties() as $p) {
            $appArray[$p->getName()] = $p->transform($appArray);
        }
        return $appArray;
    }
}