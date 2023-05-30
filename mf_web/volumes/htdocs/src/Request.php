<?php

namespace MF;

class Request
{
    private $filename;

    public function __construct() {
        $this->filename = $_GET;
    }

    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getParsedBody(): array {
        return $_POST;
    }

    public function getQueryParams(): array {
        return $_GET;
    }
}