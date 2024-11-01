<?php

declare(strict_types=1);

namespace MF\Logging;

final class Logger
{
    public function log(string $message): void
    {
        echo "MF: {$message}\n";
    }
}