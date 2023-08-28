<?php

namespace MF\Framework\Logging;

class Logger
{
    public function __construct(
        private string $prefix,
    ) {
    }

    public function log(string $message): void {
        echo $this->prefix . ': ' . $message . "\n";
    }
}