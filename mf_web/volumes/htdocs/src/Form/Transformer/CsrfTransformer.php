<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Exception\Form\WrongCsrfException;
use MF\Form\IFormElement;
use MF\Session\SessionManager;

class CsrfTransformer implements FormTransformer
{
    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, IFormElement $input): string {
        if (!isset($formRawData[$input->getName()])) {
            throw new MissingInputException($input);
        }
        if ($this->session->getCsrf() !== $formRawData[$input->getName()]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}