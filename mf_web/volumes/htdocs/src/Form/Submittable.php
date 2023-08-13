<?php

namespace MF\Form;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines a form value that can be extracted from a request’s submitted value.
 */
interface Submittable
{
    /**
     * @return \MF\Form\IFormData The form value extracted from the request, usually from the corresponding submitted
     * value. A submittable always return a value (though it can be null), or it throws an exception. This makes sure
     * all the defined form elements of a form are present in the extracted data, though they can hold a null value.
     * An IFormData instance also comes with an array of validation errors, which should be empty.
     * @throws ExtractionException If no submitted value could not found, or the found value could not be extracted.
     */
    public function extractFormData(array $requestFormData, ?array $uploadedFiles): IFormData;

    /**
     * @return mixed The default entity value, if any, in case the extracted submitted value is null. It may
     * be accessed from the form.
     */
    public function getDefaultValue(): mixed;
}