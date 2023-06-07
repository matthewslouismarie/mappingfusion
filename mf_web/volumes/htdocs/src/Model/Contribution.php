<?php

namespace MF\Model;

class Contribution
{
    private ?Uint $id;

    private Slug $authorId;

    private Slug $playableId;

    private bool $isAuthor;

    private ?LongString $summary;

    static function fromArray(array $data): self {
        return new self(
            $data['contribution_id'],
            $data['contribution_author_id'],
            $data['contribution_playable_id'],
            $data['contribution_is_author'],
            $data['contribution_summary'],
        );
    }

    public function __construct(
        ?int $id,
        string $authorId,
        string $playableId,
        bool $isAuthor,
        ?string $summary = null,
    ) {
        $this->id = $id !== null ? new Uint($id) : null;
        $this->authorId = new Slug($authorId);
        $this->playableId = new Slug($playableId);
        $this->isAuthor = $isAuthor;
        $this->summary = null !== $summary ? new LongString($summary) : null;
    }

    public function toArray(): array {
        return [
            'contribution_id' => $this->id?->__toString(),
            'contribution_author_id' => $this->authorId->__toString(),
            'contribution_playable_id' => $this->playableId->__toString(),
            'contribution_is_author' => $this->isAuthor,
            'contribution_summary' => $this->summary?->__toString(),
        ];
    }
}