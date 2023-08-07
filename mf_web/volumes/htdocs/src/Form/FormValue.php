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

    public function getData(): mixed {
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