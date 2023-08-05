<?php

namespace MF\Form;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Defines a form value that can be extracted from a request’s submitted value.
 */
interface Submittable
{
    /**
     * @return FormValue The form value extracted from the request, usually from the corresponding submitted value.
     * @throws MissingInputException If no submitted value could not found.
     */
    public function extractFormData(ServerRequestInterface $request): IFormData;

    /**
     * @return mixed The default entity value, if any, in case the extracted submitted value is null. It may
     * be accessed from the form.
     */
    public function getDefaultValue(): mixed;
}