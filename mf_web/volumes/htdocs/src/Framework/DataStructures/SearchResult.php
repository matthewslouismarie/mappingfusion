<?php

namespace MF\Framework\DataStructures;

class SearchResult
{
    /**
     * @param array<Searchable>
     */
    public function __construct(
        private array $searchables,
    ) {
    }

    /**
     * @return array<Searchable>
     */
    public function getSearchables(): array {
        return $this->searchables;
    }
}