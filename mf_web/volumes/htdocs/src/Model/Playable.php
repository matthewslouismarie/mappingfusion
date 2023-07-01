<?php

namespace MF\Model;
use DateTimeImmutable;
use OutOfBoundsException;

// @todo Separate date and time for release? In case time is unknown.
class Playable implements Entity
{
    private Slug $id;

    private LongString $name;

    private DateTimeImmutable $releaseDateTime;
    
    private ?Slug $gameId;

    private ?array $storedAuthors;

    private ?Playable $storedGame;

    static function fromArray(array $data, string $prefix = 'playable'): self {
        $game = isset($data["{$prefix}_game_name"]) ? Playable::fromArray($data, "{$prefix}_game") : null;

        return new self(
            $data["{$prefix}_id"],
            $data["{$prefix}_name"],
            new DateTimeImmutable($data["{$prefix}_release_date_time"]),
            $data["{$prefix}_game_id"],
            null,
            $game,
        );
    }

    public function __construct(
        ?string $id,
        string $name,
        DateTimeImmutable $releaseDateTime,
        ?string $gameId,
        ?array $storedAuthors = null,
        ?Playable $storedGame = null,
    ) {
        $this->id = null !== $id ? new Slug($id) : new Slug($name, true);
        $this->name = new LongString($name);
        $this->releaseDateTime = $releaseDateTime;
        $this->gameId = null !== $gameId ? new Slug($gameId) : null;
        $this->storedAuthors = $storedAuthors;
        $this->storedGame = $storedGame;
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getName(): string {
        return $this->name->__toString();
    }

    public function getReleaseDateTime(): DateTimeImmutable {
        return $this->releaseDateTime;
    }

    public function getStoredAuthors(): ?array {
        return $this->storedAuthors;
    }

    public function getStoredGame(): ?Playable {
        return $this->storedGame;
    }

    public function toArray(string $prefix = 'playable'): array {
        return [
            "{$prefix}_id" => $this->id->__toString(),
            "{$prefix}_name" => $this->name->__toString(),
            "{$prefix}_game_id" => $this->gameId?->__toString(),
            "{$prefix}_release_date_time" => $this->releaseDateTime->format('Y-m-d H:m:s'),
        ];
    }
}