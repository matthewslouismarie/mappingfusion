<?php

namespace MF\Model;
use MF\Enum\ModelPropertyType;

class PlayableDefinition implements ModelDefinition
{
    public function __construct(
        private string $name = 'playable',
    ) {
    }

    public function getName(): string {
        return $this->name;
    }

    public function getProperties(): array {
        return [
            new ModelProperty(
                'id',
                ModelPropertyType::VARCHAR,
            ),
            new ModelProperty(
                'name',
                ModelPropertyType::VARCHAR,
            ),
            new ModelProperty(
                'release_date_time',
                ModelPropertyType::DATETIME,
            ),
            new ModelProperty(
                'game_id',
                ModelPropertyType::VARCHAR,
            ),
        ];
    }

    public function getStoredData(): array {
        return [
            'stored_game' => self::class,
        ];
    }
}