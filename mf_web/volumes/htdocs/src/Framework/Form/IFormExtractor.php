<?php

namespace MF\Framework\Form;

use MF\Framework\Form\DataStructures\IFormData;

/**
 * Extract and validate submitted form data from a request.
 */
interface IFormExtractor
{
    /**
     * @return \MF\Framework\Form\DataStructures\IFormData An object containing the submitted data alongside any validation failures if relevant.
     * If no data could be extracted data (not even null), it MUST throw an ExtractionException.
     * An IFormData instance also comes with an array of validation failures, which should be empty.
     * @throws ExtractionException If no submitted value could not found, or the found value could not be extracted.
     */
    public function extractFromRequest(array $requestFormData, ?array $uploadedFiles): IFormData;
}