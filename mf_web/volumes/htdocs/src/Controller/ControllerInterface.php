<?php

namespace MF\Controller;

use MF\Enum\Clearance;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface
{
    public function generateResponse(ServerRequestInterface $request, array $routeParams): ResponseInterface;

    public function getAccessControl(): Clearance;
}