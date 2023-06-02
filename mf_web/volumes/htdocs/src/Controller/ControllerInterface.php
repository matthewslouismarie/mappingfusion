<?php

namespace MF\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface
{
    public function generateResponse(ServerRequestInterface $request): ResponseInterface;

    public function getAccessControl(): int;
}