<?php

namespace MF\Exception\Form;

use RuntimeException;

class WrongCsrfException extends RuntimeException implements ExtractionException
{
    public function getUserErrorMessage(): string {
        return 'Le formulaire n’a pas pu être validé.';
    }
}