<?php

namespace MF\Form;

use MF\Form\FormValue;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Defines a submittable whose value is only defined by its children.
 */
class FormDefinition implements Submittable
{
    private array $children;

    private ?array $defaultValue;

    public function __construct(
        array $children = [],
        mixed $defaultValue = null,
    ) {
        $this->children = $children;
        $this->defaultValue = $defaultValue;
    }

    public function extractSubmittedValue(ServerRequestInterface $request): FormArray {
        $formArray = [];
        foreach ($this->children as $child) {
            if (isset($model[$child->getName()])) {
                throw new RuntimeException();
            }
            $formArray[$child->getName()] = $child->extractSubmittedValue($request);
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

    public function generateFormValue(array $appArray): FormValue {
        $formData = [];
        foreach ($this->children as $c) {
            $formData[$c->getName()] = new FormValue($appArray[$c->getName()] ?? null);
        }
        return new FormValue($formData);
    }
}