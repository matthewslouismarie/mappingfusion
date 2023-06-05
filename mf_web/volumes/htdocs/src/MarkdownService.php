<?php

namespace MF;

use Michelf\Markdown;

class MarkdownService
{
    private Markdown $parser;

    public function __construct() {
        $this->parser = new Markdown();
        $this->parser->empty_element_suffix = '>';
    }
    public function format(string $text): string {
        return $this->parser->transform($text);
    }

    public function getText(string $text): string {
        return strip_tags($this->format($text));
    }
}