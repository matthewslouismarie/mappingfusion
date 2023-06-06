<?php

namespace MF\Model;

class Review
{
    private ?Uint $id;

    private Slug $playableId;

    private Rating $rating;

    private ?string $body;

    private ?string $cons;

    private ?string $pros;

    static function fromArray(array $data): self {
        return new self(
            null !== $data['p_id'] ? new Uint($data['p_id']) : null,
            new Slug($data['p_playable_id']),
            new Rating($data['p_rating']),
            $data['p_body'],
            $data['p_cons'],
            $data['p_pros'],
        );
    }

    public function __construct(
        ?Uint $id,
        Slug $playableId,
        Rating $rating,
        ?string $body = null,
        ?string $cons = null,
        ?string $pros = null,
    ) {
        $this->id = $id;
        $this->playableId = $playableId;
        $this->rating = $rating;
        $this->body = $body;
        $this->cons = $cons;
        $this->pros = $pros;
    }

    public function getId(): ?int {
        return $this->id->toInt();
    }

    public function getPlayableId(): Slug {
        return $this->playableId;
    }

    public function getRating(): float {
        return $this->rating->toFloat();
    }

    public function getBody(): ?string {
        return $this->body;
    }

    public function getPros(): ?string {
        return $this->pros;
    }

    public function getCons(): ?string {
        return $this->cons;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id?->toInt(),
            'p_playable_id' => $this->playableId->__toString(),
            'p_rating' => $this->rating->toFloat(),
            'p_body' => $this->body,
            'p_cons' => $this->cons,
            'p_pros' => $this->pros,
        ];
    }
}