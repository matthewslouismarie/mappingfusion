<?php

namespace MF\Framework\Form\Transformer;

use MF\Framework\Form\Exceptions\MissingInputException;
use MF\Framework\Form\Exceptions\WrongCsrfException;
use MF\Session\SessionManager;

class CsrfTransformer implements IFormTransformer
{
    const CSRF_FORM_ELEMENT_NAME = '_csrf';

    public function __construct(
        private SessionManager $session,
    ) {
    }

    public function extractValueFromRequest(array $formRawData, array $uploadedFiles): string {
        if (!isset($formRawData[self::CSRF_FORM_ELEMENT_NAME])) {
            throw new MissingInputException(self::CSRF_FORM_ELEMENT_NAME);
        }
        if ($this->session->getCsrf() !== $formRawData[self::CSRF_FORM_ELEMENT_NAME]) {
            throw new WrongCsrfException();
        }

        return $this->session->getCsrf();
    }
}