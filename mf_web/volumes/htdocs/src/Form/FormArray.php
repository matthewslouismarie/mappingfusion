<?php

namespace MF\Form;

class FormArray implements IFormData
{
    /**
     * @param \MF\Form\IFormData[] An array of submissions, indexed by the name of the form element that extracted it.
     */
    public function __construct(
        private array $submissions,
    ) {
    }

    public function getData(): array {
        $data = [];
        foreach ($this->submissions as $key => $s) {
            $data[$key] = $s->getData();
        }

        return $data;
    }

    public function getFormData(string $name): ?FormValue {
        return $this->submissions[$name] ?? null;
    }

    public function getValidationFailures(): array {
        $errors = [];
        foreach ($this->submissions as $key => $formValue) {
            $errors[$key] = $formValue->getValidationFailures();
        }
        return $errors;
    }

    public function hasErrors(): bool {
        foreach ($this->submissions as $formData) {
            if ($formData->hasErrors()) {
                return true;
            }
        }
        return false;
    }
}