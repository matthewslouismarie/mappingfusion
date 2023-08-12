<?php

namespace MF\Form\Transformer;

use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

class CheckboxTransformer implements FormTransformer
{
    public function extractValueFromRequest(ServerRequestInterface $request, IFormElement $input): bool {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            return false;
        }
        return 'on' === $request->getParsedBody()[$input->getName()] ? true : false;
    }
}