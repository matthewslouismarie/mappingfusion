<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

class StringTransformer implements FormTransformer
{
    /**
     * @return string The submitted, non-empty string.
     * @return null If an empty string was submitted.
     */
    public function extractValueFromRequest(ServerRequestInterface $request, IFormElement $input): ?string {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            throw new MissingInputException();
        }
        $submittedString = $request->getParsedBody()[$input->getName()];
        return '' !== $submittedString ? $submittedString : null;
    }
}