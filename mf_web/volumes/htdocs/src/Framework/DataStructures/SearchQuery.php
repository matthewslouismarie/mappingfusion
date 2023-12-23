<?php

namespace MF\Framework\DataStructures;

use UnexpectedValueException;

class SearchQuery
{
    CONST ACCEPTED_DELIMITERS = [
        ',',
        ';',
        ' ',
    ];

    CONST ACCEPTED_MODIFIERS = [
        '\'',
        '’',
    ];

    CONST MODIFIER = '"';

    CONST SEPARATOR = ' ';

    /**
     * @todo Use Ds\Set.
     */
    private array $keywords;

    public function __construct(string $query) {
        $convertedModifiers = str_replace(self::ACCEPTED_MODIFIERS, self::MODIFIER, $query);
        $modifiedQuery = explode(self::MODIFIER, $convertedModifiers);
        $keywords = [];
        for ($i = 0; $i < count($modifiedQuery); $i++) {
            if ($i % 2 == 0) {
                $convertedQuery = str_replace(self::ACCEPTED_DELIMITERS, self::SEPARATOR, $modifiedQuery[$i]);
                $convertedQuery = preg_replace("#[[:punct:]]#", self::SEPARATOR, $convertedQuery);
                $keywords = array_merge($keywords, array_filter(explode(self::SEPARATOR, $convertedQuery)));
            } elseif ('' !== $modifiedQuery[$i]) {
                $keywords[] = $modifiedQuery[$i];
            }
        }
        $this->keywords = ($keywords);
        if (count($this->keywords) < 1) {
            throw new UnexpectedValueException();
        }
    }

    public function __toString(): string {
        $words = [];
        foreach ($this->keywords as $kw) {
            if ($this->isModified($kw)) {
                $words[] = '"' . $kw . '"';
            } else {
                $words[] = $kw;
            }
        }

        return implode(self::SEPARATOR, $words);
    }

    /**
     * @return array<string>
     */
    public function getKeywords(): array {
        return $this->keywords;
    }

    /**
     * @return int The total number of characters of all the keywords, separators
     * between keywords excluded.
     */
    public function getTotalLength(): int {
        $length = 0;
        foreach ($this->keywords as $kw) {
            $length += mb_strlen($kw);
        }
        return $length;
    }

    private function isModified(string $keyword): bool {
        foreach (self::ACCEPTED_DELIMITERS as $del) {
            if (str_contains($keyword, $del)) {
                return true;
            }
        }
        if (str_contains($keyword, self::SEPARATOR)) {
            return true;
        }
        return false;
    }
}