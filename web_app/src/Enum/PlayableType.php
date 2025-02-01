<?php

namespace MF\Enum;

use DomainException;

enum PlayableType: string
{
    case Addon = 'Addon';

    case Engine = 'Moteur';

    case Other = 'Autre';
    
    case Map = 'Map';

    case Mod = 'Mod';

    case Standalone = 'Standalone';

    public static function fromString(string $type)
    {
        foreach (self::cases() as $case) {
            if ($case->value === $type) {
                return $case;
            }
        }
        throw new DomainException();
    }
}