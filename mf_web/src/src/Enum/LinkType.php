<?php

namespace MF\Enum;

use DomainException;

enum LinkType: string
{
    case Download = 'download';

    case HomePage = 'homepage';

    case Other = 'other';

    public static function fromString(string $type) {
        foreach (self::cases() as $case) {
            if ($case->value === $type) {
                return $case;
            }
        }
        throw new DomainException();
    }
}