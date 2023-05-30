<?php

namespace MF;

class RequestFactory
{
    public function createRequest(): Request {
        return new Request();
    }
}