<?php

namespace MF;

use Michelf\Markdown;

class MarkdownService
{
    const ADVICE_MARKER = '<!-- Conseil -->';

    private Markdown $parser;

    public function __construct() {
        $this->parser = new Markdown();
        $this->parser->empty_element_suffix = '>';
    }

    public function format(string $text): string {
        return $this->parser->transform($text);
    }

    /**
     * @return string[]
     */
    public function formatArticleBody(string $text): array {
        $split = explode(self::ADVICE_MARKER, $text);
        return [
            'main' => $this->format($split[0]),
            'advice' => count($split) > 1 ? $this->format($split[1]) : null,
        ];
    }

    public function getText(string $text): string {
        return strip_tags($this->format($text));
    }
}