<?php

namespace MF\Form;

class FormArray implements IFormData
{
    /**
     * @param \MF\Form\Submittable[] An array fo submittable, indexed by the name of the form element that extracted it.
     */
    public function __construct(
        private array $submissions,
    ) {
    }

    public function getData(): array {
        $data = [];
        foreach ($this->submissions as $key => $formValue) {
            $data[$key] = $formValue->getData();
        }

        return $data;
    }

    public function getFormValue(string $name): ?FormValue {
        return $this->submissions[$name] ?? null;
    }

    public function getErrors(): array {
        $errors = [];
        foreach ($this->submissions as $key => $formValue) {
            $errors[$key] = $formValue->getErrors();
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