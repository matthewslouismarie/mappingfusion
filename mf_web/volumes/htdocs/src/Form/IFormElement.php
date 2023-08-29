<?php

namespace MF\Form;

use MF\Form\Transformer\FormTransformer;

/**
 * Defines a form data factory with a name and an associated transformer.
 */
interface IFormElement extends IFormDataFactory
{
    /**
     * @return string The form identifier for the form element.
     */
    public function getName(): string;

    public function getTransformer(): FormTransformer;
}