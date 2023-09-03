<?php

namespace MF\Framework\Form\DataStructures;

class FormArray implements IFormData
{
    /**
     * @param \MF\Framework\Form\DataStructures\IFormData[] An array of submissions, indexed by the name of the form element that extracted it.
     */
    public function __construct(
        private array $submissions,
    ) {
    }

    public function getContent(): array {
        $data = [];
        foreach ($this->submissions as $key => $s) {
            $data[$key] = $s->getContent();
        }

        return $data;
    }

    public function getChild(string $name): ?IFormData {
        return $this->submissions[$name] ?? null;
    }

    /**
     * @return \MF\Framework\Form\DataStructures\IFormData[]
     */
    public function getChildren(): array {
        return $this->submissions;
    }

    public function getErrors(): array {
        $errors = [];
        foreach ($this->submissions as $key => $formValue) {
            $errors[$key] = $formValue->getErrors();
        }
        return $errors;
    }

    public function getValue(): mixed {
        return $this->submissions;
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