<?php

namespace MF\Form;

/**
 * Data fetched and transformed from a request along with its errors, if any.
 * @todo Rename to Submission or DataSubmission or FormSubmission…
 */
interface IFormData
{
    public function getData(): mixed;

    /**
     * @return string[] An array of string errors, or of arrays of string errors, regarding the data. If the
     */
    public function getErrors(): array;

    public function hasErrors(): bool;
}