<?php

namespace MF\Form\Transformer;

use MF\Exception\Form\MissingInputException;
use MF\Exception\Form\WrongCsrfException;
use MF\Session\SessionManager;

class CsrfTransformer implements FormTransformer
{
    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles, string $inputName): string {
        if (!isset($formRawData[$inputName])) {
            throw new MissingInputException($inputName);
        }
        if ($this->session->getCsrf() !== $formRawData[$inputName]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}