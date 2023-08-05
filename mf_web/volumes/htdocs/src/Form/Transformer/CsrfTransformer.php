<?php

namespace MF\Form\Transformer;

use MF\Exception\InvalidFormException\MissingInputException;
use MF\Form\FormElement;
use MF\Http\SessionManager;
use Psr\Http\Message\ServerRequestInterface;

class CsrfTransformer implements FormTransformer
{
    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(ServerRequestInterface $request, FormElement $input): string {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            throw new MissingInputException();
        }

        return $this->session->getCsrf();
    }
}