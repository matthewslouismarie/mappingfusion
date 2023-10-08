<?php

namespace MF\Exception\Http;

use MF\Framework\Http\Exception\IHttpException;
use RuntimeException;

class NotFoundException extends RuntimeException implements IHttpException
{
    public function getStatusCode(): int {
        return 404;
    }
}