<?php

namespace MF\Form\Transformer;

use DateTimeImmutable;
use MF\Exception\InvalidFormException\MissingInputException;
use MF\Form\FormElement;
use Psr\Http\Message\ServerRequestInterface;

class DateTimeTransformer implements FormTransformer
{
    public function extractValueFromRequest(ServerRequestInterface $request, FormElement $input): ?DateTimeImmutable {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            throw new MissingInputException();
        }

        $submittedString = $request->getParsedBody()[$input->getName()];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}