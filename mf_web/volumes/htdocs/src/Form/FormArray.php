<?php

namespace MF\Form;

use BadMethodCallException;

class FormArray implements IFormData
{
    public function __construct(
        private array $formDatas,
    ) {
    }

    public function getData(): array {
        $data = [];
        foreach ($this->formDatas as $key => $formValue) {
            $data[$key] = $formValue->getData();
        }

        return $data;
    }

    public function getFormValue(string $name): ?FormValue {
        return $this->formDatas[$name] ?? null;
    }

    public function getErrors(): array {
        $errors = [];
        foreach ($this->formDatas as $key => $formValue) {
            $errors[$key] = $formValue->getErrors();
        }
        return $errors;
    }

    public function hasErrors(): bool {
        foreach ($this->formDatas as $formData) {
            if ($formData->hasErrors()) {
                return true;
            }
        }
        return false;
    }
}