<?php

namespace MF\Framework\Form\Exceptions;

class WrongCsrfException extends ExtractionException
{
    public function getUserErrorMessage(): string {
        return 'Le formulaire n’a pas pu être validé.';
    }
}