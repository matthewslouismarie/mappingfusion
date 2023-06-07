<?php

namespace MF\Model;

use OutOfBoundsException;
use TypeError;

class Playable implements Entity
{
    private Slug $id;

    private LongString $name;
    
    private ?Slug $gameId;

    private ?array $storedAuthors;

    private ?Playable $storedGame;

    static function fromArray(array $data): self {
        $gameName = $data['playable_game_name'] ?? null;
        $game = null !== $gameName ? new Playable($data['playable_game_id'], $gameName, null) : null;

        return new self(
            $data['playable_id'],
            $data['playable_name'],
            $data['playable_game_id'],
            null,
            $game,
        );
    }

    public function __construct(
        ?string $id,
        string $name,
        ?string $gameId,
        ?array $storedAuthors = null,
        ?Playable $storedGame = null,
    ) {
        $this->id = null !== $id ? new Slug($id) : new Slug($name, true);
        $this->name = new LongString($name);
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

    public function getStoredGame(): ?Playable {
        return $this->storedGame;
    }

    public function toArray(): array {
        return [
            'playable_id' => $this->id->__toString(),
            'playable_name' => $this->name->__toString(),
            'playable_game_id' => $this->gameId?->__toString(),
        ];
    }
}