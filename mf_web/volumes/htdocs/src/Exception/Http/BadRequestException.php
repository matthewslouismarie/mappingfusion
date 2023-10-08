<?php

namespace MF\Exception\Http;

use MF\Framework\Http\Exception\IHttpException;
use RuntimeException;

class BadRequestException extends RuntimeException implements IHttpException
{
    public function getStatusCode(): int {
        return 400;
    }
}