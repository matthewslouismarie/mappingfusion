<?php

namespace MF\Model;
use OutOfBoundsException;
use TypeError;

class Review
{
    private ?Uint $id;

    private Slug $playableId;

    private Rating $rating;

    private ?string $body;

    private ?string $cons;

    private ?string $pros;

    private ?Playable $storedPlayable;

    static function fromArray(array $data): self {
        try {
            $playable = Playable::fromArray($data);
        } catch (OutOfBoundsException|TypeError $e) {
            $playable = null;
        }
        return new self(
            $data['review_id'],
            $data['review_playable_id'],
            $data['review_rating'],
            $data['review_body'],
            $data['review_cons'],
            $data['review_pros'],
            $playable,
        );
    }

    public function __construct(
        ?int $id,
        string $playableId,
        float $rating,
        ?string $body = null,
        ?string $cons = null,
        ?string $pros = null,
        ?Playable $storedPlayable = null,
    ) {
        $this->id = null !== $id ? new Uint($id) : null;
        $this->playableId = new Slug($playableId);
        $this->rating = new Rating($rating);
        $this->body = $body;
        $this->cons = $cons;
        $this->pros = $pros;
        $this->storedPlayable = $storedPlayable;
    }

    public function getId(): ?int {
        return $this->id?->toInt();
    }

    public function getPlayableId(): string {
        return $this->playableId->__toString();
    }

    public function getRating(): float {
        return $this->rating->toFloat();
    }

    public function getBody(): ?string {
        return $this->body;
    }

    public function getCons(): ?string {
        return $this->cons;
    }

    public function getPros(): ?string {
        return $this->pros;
    }

    public function getStoredPlayable(): ?Playable {
        return $this->storedPlayable;
    }

    public function toArray(): array {
        $playable = $this->storedPlayable?->toArray() ?? [];
        return $playable + [
            'review_id' => $this->id?->toInt(),
            'review_playable_id' => $this->playableId->__toString(),
            'review_rating' => $this->rating->toFloat(),
            'review_body' => $this->body,
            'review_cons' => $this->cons,
            'review_pros' => $this->pros,
        ];
    }
}