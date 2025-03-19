<?php

declare(strict_types=1);

namespace MF\Logging;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\ErrorHandling\LoggedException;
use Throwable;

/**
 * @todo Should implement Logger interface.
 * @todo Could be moved in lm-web-framework.
 */
final class Logger
{
    public function __construct(
        private Configuration $configuration,
    ) {
    }

    public function log(Throwable $throwable): void
    {
        if ('cli' === php_sapi_name()) {
            echo $throwable->__toString();
        } else {
            $throwableArray = [
                'hash' => hash('sha256', $throwable->__toString()),
                'timestamp' => time(),
                'code' => $throwable->getCode(),
                'line' => $throwable->getLine(),
                'file' => $throwable->getFile(),
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTrace(),
            ];
            file_put_contents($this->configuration->getPathOfAppDirectory() . '/' . $this->configuration->getSetting('varPath'), json_encode($throwableArray, JSON_THROW_ON_ERROR) . "\n", FILE_APPEND);
        }
    }
}