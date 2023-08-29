<?php

namespace MF\Form;

use InvalidArgumentException;

/**
 * Extracts a FormArray from HTTP requests, and converts arrays into FormArray-s.
 * It is
 */
class Form implements Submittable
{
    private array $children;

    private ?string $ignoreValueOf;

    /**
     * @param IFormElement[] $children An array of Form Element constituting the form.
     * @param string|null $ignoreValueOf The name of a Form Element to ignore whose value it extracts should be ignored.
     * @throws \InvalidArgumentException If the given children are invalid.
     */
    public function __construct(
        array $children = [],
        ?string $ignoreValueOf = null,
    ) {
        $definedChildNames = [];
        foreach ($children as $c) {
            if (!($c instanceof IFormElement)) {
                throw new InvalidArgumentException('The form must be initialized with an array of IFormElement instances.');
            }
            if (in_array($c->getName(), $definedChildNames, true)) {
                throw new InvalidArgumentException('The form cannot have two direct children with identical names.');
            }
            $definedChildNames[] = $c->getName();
        }

        $this->children = $children;
        $this->ignoreValueOf = $ignoreValueOf;
    }

    public function extractFormData(array $requestFormData, ?array $uploadedFiles = null): FormArray {
        $formArray = [];
        foreach ($this->children as $child) {
            if ($child->getName() === $this->ignoreValueOf) {
                $child->extractFormData($requestFormData, $uploadedFiles ?? []);
            } else {
                $formArray[$child->getName()] = $child->extractFormData($requestFormData, $uploadedFiles ?? []);
            }
        }
        return new FormArray($formArray);
    }

    public function extractNoValidate(array $requestFormData, ?array $uploadedFiles = null): array {
        $formArray = [];
        foreach ($this->children as $child) {
            if ($child->getName() === $this->ignoreValueOf) {
                $child->extractFormData($requestFormData, $uploadedFiles ?? []);
            } else {
                $formArray[$child->getName()] = $child->extractFormData($requestFormData, $uploadedFiles ?? [])->getData();
            }
        }
        return $formArray;
    }

    public function getChild(string $id): IFormElement {
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
}