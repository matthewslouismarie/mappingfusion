<?php

namespace MF;

use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\CommonMarkConverter;

class MarkdownService
{
    const ADVICE_MARKER = '<!--- Conseil -->';

    private CommonMarkConverter $parser;

    public function __construct() {
        $this->parser = new CommonMarkConverter([
            'allow_unsafe_links' => false,
        ]);
        $this->parser->getEnvironment()->addExtension(new EmojiExtension());
    }

    public function format(string $text): string {
        return $this->parser->convert($text);
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