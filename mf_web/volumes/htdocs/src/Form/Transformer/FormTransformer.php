<?php

namespace MF\Form\Transformer;

use MF\Form\FormElement;
use Psr\Http\Message\ServerRequestInterface;

interface FormTransformer
{
    /**
     * Extract, from the given HTTP request, a form value from a submitted value.
     *
     * @return mixed The submitted value transformed to a form value.
     * @return null If the user submitted a null value (or a submitted value that evalutates to a null form value).
     * @throws MissingInputException If no value could be extracted from the request.
     * @throws InvalidInputException If the submitted value was found but could not be converted to a form value.
     */
    public function extractValueFromRequest(ServerRequestInterface $request, FormElement $input): mixed;
}