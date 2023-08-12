<?php

namespace MF\Form;

use MF\Form\Transformer\FormTransformer;

/**
 * Defines a submittable individual element with no children.
 */
interface IFormElement extends Submittable
{
    /**
     * @return string The form identifier for the form element.
     * @todo Must not be able to return non valid names?
     */
    public function getName(): string;

    public function getTransformer(): FormTransformer;
}