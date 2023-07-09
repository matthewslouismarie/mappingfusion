<?php

namespace MF;
use InvalidArgumentException;
use UnexpectedValueException;

class Form
{
    public function nullifyEmptyStrings(array $data): array {
        return array_map(function (?string $value) {
            return '' !== $value ? $value : null;
        }, $data);
    }

    public function fromForm(array $formData): array
    {
        $data = $this->nullifyEmptyStrings($formData);

        // foreach ($data as $key => $value) {
        //     $parts = explode('_', $key);

        //     $formData = $this->prependKeys($parts, $formData, $value);
        // }

        return $formData;
    }

    /**
     * @todo Unused. Already implemented by PHP. Could be removed.
     */
    private function prependKeys(array $parts, array $data, mixed $value, int $start = 0): array {
        $prefix = null;
        for ($i = $start; $i < count($parts); $i++) {
            $isNumber = ctype_digit($parts[$i]);
            
            if ($isNumber) {
                var_dump($parts[$i]);
                if (!isset($data[$prefix])) {
                    $data[$prefix] = [];
                } elseif (!is_array($data[$prefix])) {
                    throw new UnexpectedValueException();
                }
                $data[$prefix][$parts[$i]] = $this->prependKeys($parts, $data[$prefix], $value, $i + 1);

                return $data;
            } else {
                $prefix = null !== $prefix ? $prefix . '_' . $parts[$i] : $parts[$i];
            }
        }
        $data[$prefix] = $value;     
        return $data;
    }
}