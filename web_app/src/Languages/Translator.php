<?php

namespace MF\Languages;

use LM\WebFramework\Configuration\Configuration;

class Translator
{
    private array $translations;

    public function __construct(
        Configuration $configuration,
    ) {
        if ($configuration->getLanguage() != 'fr') {
            $this->translations = include_once $configuration->getPathOfAppDirectory() . '/translations/' . $configuration->getLanguage() . '.php';
        }
    }

    public function translate(string $text): string
    {
        return isset($this->translations[$text]) ? $this->translations[$text] : $text;
    }
}