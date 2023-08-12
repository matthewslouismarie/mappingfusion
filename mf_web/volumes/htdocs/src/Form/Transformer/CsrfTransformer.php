<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Exception\Form\WrongCsrfException;
use MF\Form\IFormElement;
use MF\Session\SessionManager;
use Psr\Http\Message\ServerRequestInterface;

class CsrfTransformer implements FormTransformer
{
    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(ServerRequestInterface $request, IFormElement $input): string {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            throw new MissingInputException();
        }
        if ($this->session->getCsrf() !== $request->getParsedBody()[$input->getName()]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}