<?php

namespace MF\Model;

class Playable implements Entity
{
    private Slug $id;

    private LongString $name;
    
    private ?Slug $authorId;
    
    private ?Slug $gameId;

    static function fromArray(array $data): self {
        return new self(
            $data['playable_id'] !== null ? new Slug($data['playable_id']) : null,
            new LongString($data['playable_name']),
            null !== $data['playable_author_id'] ? new Slug($data['playable_author_id']) : null,
            null !== $data['playable_game_id'] ? new Slug($data['playable_game_id']) : null,
        );
    }

    public function __construct(
        ?Slug $id,
        LongString $name,
        ?Slug $authorId,
        ?Slug $gameId,
    ) {
        $this->id = $id ?? new Slug($name);
        $this->name = $name;
        $this->authorId = $authorId;
        $this->gameId = $gameId;
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
            'playable_author_id' => $this->authorId?->__toString(),
            'playable_game_id' => $this->gameId?->__toString(),
        ];
    }
}