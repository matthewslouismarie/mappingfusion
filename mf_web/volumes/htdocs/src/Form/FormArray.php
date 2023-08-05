<?php

namespace MF\Form;

class FormArray extends FormValue
{
    public function __construct(
        private array $formDatas,
    ) {
    }

    public function getValue(): array {
        return $this->formDatas;
    }

    public function getFormValue(string $name): ?FormValue {
        return $this->formDatas[$name] ?? null;
    }

    public function getErrors(): array {
        $errors = [];
        foreach ($this->formDatas as $formData) {
            $errors += $formData->getErrors();
        }
        return $errors;
    }
}