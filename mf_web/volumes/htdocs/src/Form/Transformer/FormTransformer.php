<?php

namespace MF\Form\Transformer;

use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

interface FormTransformer
{
    /**
     * Extract, from the given HTTP request, a form value from a submitted value.
     *
     * @return mixed The submitted value transformed to a form value.
     * @return null If the user submitted a null value (or a submitted value that evalutates to a null form value).
     * @throws MissingInputException If no value could be extracted from the request.
     */
    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): mixed;
}