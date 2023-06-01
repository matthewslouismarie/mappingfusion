<?php

namespace MF;

use Michelf\Markdown;

class MarkdownService
{
    public function format(string $text): string {
        return Markdown::defaultTransform($text);
    }
}