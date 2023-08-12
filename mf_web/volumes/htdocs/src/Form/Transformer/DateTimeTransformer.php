<?php

namespace MF\Form\Transformer;

use DateTimeImmutable;
use MF\Exception\Form\MissingInputException;
use MF\Form\IFormElement;
use Psr\Http\Message\ServerRequestInterface;

class DateTimeTransformer implements FormTransformer
{
    public function extractValueFromRequest(ServerRequestInterface $request, IFormElement $input): ?DateTimeImmutable {
        if (!isset($request->getParsedBody()[$input->getName()])) {
            throw new MissingInputException();
        }

        $submittedString = $request->getParsedBody()[$input->getName()];

        return '' !== $submittedString ? new DateTimeImmutable($submittedString) : null;
    }
}