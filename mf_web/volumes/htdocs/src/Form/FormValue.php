<?php

namespace MF\Form;

/**
 * @todo Rename and remove "value" from name? Like FormRow?
 * @todo Create a separate class for form array and form value?
 */
class FormValue implements IFormData
{
    public function __construct(
        private mixed $value,
        private array $errors = [],
    ) {
    }

    public function getData(?array $hereWeGo = null): mixed {
        if (is_array($this->value)) {
            $appArray = [];
            foreach ($hereWeGo ?? $this->value as $k => $v) {
                if ($v instanceof IFormData) {
                    $appArray[$k] = $v->getData();
                } elseif (is_array($v)) {
                    $appArray[$k] = $this->getData($v);
                } else {
                    $appArray[$k] = $v;
                }
            }
            return $appArray;
        }
        return $this->value;
    }

    public function getArrayValue(): array {
        return $this->value;
    }

    public function getBoolValue(): ?bool {
        return is_bool($this->value) ? $this->value : null;
    }

    public function getFormValue(string $name): ?FormValue {
        return $this->value[$name] ?? null;
    }

    public function getStringValue(): ?string {
        return is_string($this->value) ? $this->value : null;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public function hasErrors(): bool {
        return count($this->errors) > 0;
    }

    public function isNull(): bool {
        return null === $this->value;
    }
}