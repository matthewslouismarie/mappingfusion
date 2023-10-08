<?php

namespace MF\Framework\Http\Exception;

interface IHttpException
{
    public function getStatusCode(): int;
}