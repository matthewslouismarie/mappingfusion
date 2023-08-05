<?php

namespace MF\Form;

use MF\Exception\InvalidFormException\InvalidInputException;
use MF\Form\Transformer\FormTransformer;
use Psr\Http\Message\ServerRequestInterface;

class StdFormElement implements FormElement
{
    private string $name;

    private mixed $defaultValue;

    private FormTransformer $transformer;

    private bool $isRequired;

    private array $validators;

    public function __construct(
        string $name,
        FormTransformer $transformer,
        mixed $defaultValue = null,
        bool $isRequired = true,
        array $validators = [],
    ) {
        $this->name = $name;
        $this->transformer = $transformer;
        $this->defaultValue = $defaultValue;
        $this->isRequired = $isRequired;
        $this->validators = $validators;
    }

    public function extractFormData(ServerRequestInterface $request): FormValue {
        try {
            $transformedData = $this->transformer->extractValueFromRequest($request, $this) ?? $this->defaultValue;

            $errors = $this->validate($transformedData);
            
            return new FormValue($transformedData, $errors);
        } catch (InvalidInputException $e) {
            $errors[] = 'Une erreur est arrivé.';
            return new FormValue(null, $errors);
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDefaultValue(): mixed {
        return $this->defaultValue;
    }

    public function getTransformer(): FormTransformer {
        return $this->transformer;
    }

    public function isRequired(): bool {
        return $this->isRequired;
    }
    
    public function validate(mixed $transformedData): array {
        $errors = [];
        if (null === $transformedData && $this->isRequired) {
            $errors[] = 'Ce champ est requis.';
        }
        foreach ($this->validators as $v) {
            $errors += $v->validate($transformedData);
        }
        return $errors;
    }
}