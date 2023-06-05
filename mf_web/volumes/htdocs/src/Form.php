<?php

namespace MF;

class Form
{
    public function nullifyEmptyStrings(array $data): array {
        return array_map(function (?string $value) {
            return '' !== $value ? $value : null;
        }, $data);
    }
}