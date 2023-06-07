<?php

namespace MF\Model;

class Playable implements Entity
{
    private Slug $id;

    private LongString $name;
    
    private ?Slug $gameId;

    private ?array $storedAuthors;

    static function fromArray(array $data): self {
        return new self(
            $data['playable_id'],
            $data['playable_name'],
            $data['playable_game_id'],
        );
    }

    public function __construct(
        ?string $id,
        string $name,
        ?string $gameId,
        ?array $storedAuthors = null,
    ) {
        $this->id = null !== $id ? new Slug($id) : new Slug($name, true);
        $this->name = new LongString($name);
        $this->gameId = null !== $gameId ? new Slug($gameId) : null;
        $this->storedAuthors = $storedAuthors;
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getName(): string {
        return $this->name->__toString();
    }

    public function toArray(): array {
        return [
            'playable_id' => $this->id->__toString(),
            'playable_name' => $this->name->__toString(),
            'playable_game_id' => $this->gameId?->__toString(),
        ];
    }
}