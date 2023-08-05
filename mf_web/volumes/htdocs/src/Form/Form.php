<?php

namespace MF\Form;

use MF\Form\FormValue;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extracts a FormArray from HTTP requests, and converts arrays into FormArray-s.
 * It is
 */
class Form implements Submittable
{
    private array $children;

    private ?array $defaultValue;

    /**
     * @param FormElement[] $children An array of child form elements.
     * @param array defaultValue A default value for the form data.
     */
    public function __construct(
        array $children = [],
        mixed $defaultValue = null,
    ) {
        $this->children = $children;
        $this->defaultValue = $defaultValue;
    }

    public function extractFormData(ServerRequestInterface $request): FormArray {
        $formArray = [];
        foreach ($this->children as $child) {
            if (isset($model[$child->getName()])) {
                throw new RuntimeException();
            }
            $formArray[$child->getName()] = $child->extractFormData($request);
        }
        return new FormArray($formArray);
    }

    public function getChild(string $id): FormElement {
        foreach ($this->children as $child) {
            if ($child->getName() === $id) {
                return $child;
            }
        }
        throw new RuntimeException();
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }

    public function generateFormData(array $data, bool $validate = true): FormArray {
        $formData = [];
        foreach ($this->children as $c) {
            $childData = $data[$c->getName()] ?? null;
            $childErrors = $validate ? $c->validate($childData) : [];
            $formData[$c->getName()] = new FormValue($childData, $childErrors);
        }
        return new FormArray($formData);
    }
}