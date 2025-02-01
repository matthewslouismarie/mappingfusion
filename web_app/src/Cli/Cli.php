<?php

namespace MF\Cli;

use BadMethodCallException;
use LM\WebFramework\DataStructures\AppList;

class Cli
{
    private AppList $argv;

    public function __construct(
        array $argv,
    ) {
        $this->argv = new AppList($argv);
    }

    public function checkIsCli(): void
    {
        if (php_sapi_name() !== 'cli') {
            throw new BadMethodCallException('Script requires to be run with CLI.');
        }
    }

    public function contains(string $value): bool
    {
        return $this->argv->contains($value);
    }

    public function getStringParameter(int $pos): string
    {
        return $this->argv->getString($pos);
    }
}