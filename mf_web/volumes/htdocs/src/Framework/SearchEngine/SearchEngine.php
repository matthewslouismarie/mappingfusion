<?php

namespace MF\Framework\SearchEngine;

use ArrayAccess;
use MF\Framework\DataStructures\SearchQuery;

class SearchEngine
{
    /**
     * @param ArrayAccess<string, mixed> $result
     * @param array<\MF\Framework\DataStructures\Searchable> $searchables
     */
    public function rankResult(
            SearchQuery $query,
            ArrayAccess $result,
            array $searchables,
        ): float {
        $rank = .0;
        foreach ($searchables as $s) {
            if (null !== $result[$s->getName()]) {
                $k = 0;
                foreach ($query->getKeywords() as $kw) {
                    if (str_contains($result[$s->getName()], $kw)) {
                        $k += mb_strlen($kw);
                    }
                }
                $ratio = $k / $query->getTotalLength();
                $rank += (exp($ratio**2) - 1) / (exp(1) - 1) * $s->getImportance();
            }
        }
        return $rank;
    }
}