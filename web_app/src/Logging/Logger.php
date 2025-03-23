<?php

declare(strict_types=1);

namespace MF\Logging;

use LM\WebFramework\Configuration\Configuration;
use LM\WebFramework\ErrorHandling\LoggedException;
use Throwable;

/**
 * @todo Should implement Logger interface.
 * @todo Could be moved in lm-web-framework.
 * @todo Should conform to loggins standards.
 * @todo Should display to console / save in a file / other depending
 * on config.
 * @todo Should take the "log level" into account.
 * @todo Add logging level or use another library.
 */
final class Logger
{
    private string $logFilePath;
    
    private bool $isCli;

    public function __construct(
        private Configuration $config,
    ) {
        $this->logFilePath = "{$config->getPathOfAppDirectory()}/{$config->getSetting('varPath')}";
        $this->isCli = 'cli' === php_sapi_name();
    }

    public function logMessage(string $message): void
    {
        $this->isCli ? $this->displayEntry($message) : $this->saveEntry(['message' => $message]);
    }

    public function log(Throwable $throwable): void
    {
        if ('cli' === php_sapi_name()) {
            echo("{$throwable->__toString()}\n");
        } else {
            $throwableArray = [
                'code' => $throwable->getCode(),
                'line' => $throwable->getLine(),
                'file' => $throwable->getFile(),
                'message' => $throwable->getMessage(),
                'trace' => $throwable->getTrace(),
            ];
            $this->saveEntry($throwableArray);
        }
    }

    private function displayEntry(string $message): void
    {
        echo($message);
    }

    private function saveEntry(array $entryContent): void
    {
        $entryContent['timestamp'] = time();
        $entry = [
            'hash' => hash('sha256', json_encode($entryContent, JSON_THROW_ON_ERROR)),
            'content' => $entryContent,
        ];

        file_put_contents(
            $this->logFilePath,
            json_encode($entry, JSON_THROW_ON_ERROR) . "\n",
            FILE_APPEND,
        );
    }
}