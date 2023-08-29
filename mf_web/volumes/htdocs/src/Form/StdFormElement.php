<?php

namespace MF\Form;

use MF\Form\Transformer\FormTransformer;
use MF\Validator\NotNullValidator;
use MF\Validator\ValidationFailure;
use Psr\Http\Message\ServerRequestInterface;

class StdFormElement implements IFormElement
{
    private string $name;

    private FormTransformer $transformer;

    private bool $isRequired;

    private array $validators;

    public function __construct(
        string $name,
        FormTransformer $transformer,
        bool $isRequired = true,
        array $validators = [],
    ) {
        $this->name = $name;
        $this->transformer = $transformer;
        $this->isRequired = $isRequired;
        $this->validators = $validators;
    }

    public function extractFromRequest(array $requestFormData, ?array $uploadedFiles = null): StdFormData {
        $transformedData = $this->transformer->extractValueFromRequest(
            $requestFormData,
            $uploadedFiles ?? [],
            $this->name,
        );

        $errors = [];
        foreach ($this->validate($transformedData) as $failure) {
            $errors[] = $failure->getMessage();
        }
        
        return new StdFormData($transformedData, $errors);
    }

    public function getName(): string {
        return $this->name;
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
            $errors[] = new ValidationFailure('Ce champ est requis.');
        }
        foreach ($this->validators as $v) {
            if ($this->isRequired() || !($v instanceof NotNullValidator)) {
                $errors += $v->validate($transformedData);
            }
        }
        return $errors;
    }
}