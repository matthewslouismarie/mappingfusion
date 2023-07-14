<?php

namespace MF\Model;

class Review
{
    private ?Uint $id;

    private Slug $articleId;

    private Slug $playableId;

    private Rating $rating;

    private string $body;

    private string $cons;

    private string $pros;

    private ?Article $storedArticle;

    private ?Playable $storedPlayable;

    static function fromArray(array $data): self {
        $article = isset($data['article_id']) ? Article::fromArray($data) : null;
        $playable = isset($data['playable_id']) ? Playable::fromArray($data) : null;

        return new self(
            $data['review_id'],
            $data['review_article_id'],
            $data['review_playable_id'],
            $data['review_rating'],
            $data['review_body'],
            $data['review_cons'],
            $data['review_pros'],
            $article,
            $playable,
        );
    }

    public function __construct(
        ?int $id,
        string $articleId,
        string $playableId,
        float $rating,
        string $body,
        string $cons,
        string $pros,
        ?Article $storedArticle = null,
        ?Playable $storedPlayable = null,
    ) {
        $this->id = null !== $id ? new Uint($id) : null;
        $this->articleId = new Slug($articleId);
        $this->playableId = new Slug($playableId);
        $this->rating = new Rating($rating);
        $this->body = $body;
        $this->cons = $cons;
        $this->pros = $pros;
        $this->storedArticle = $storedArticle;
        $this->storedPlayable = $storedPlayable;
    }

    public function getId(): ?int {
        return $this->id?->toInt();
    }

    public function getArticleId(): string {
        return $this->articleId->__toString();
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

    public function getStoredArticle(): ?Article {
        return $this->storedArticle;
    }

    public function getStoredPlayable(): ?Playable {
        return $this->storedPlayable;
    }

    public function toArray(): array {
        $article = $this->storedArticle?->toArray() ?? [];
        $playable = $this->storedPlayable?->toArray() ?? [];
        return $article + $playable + [
            'review_id' => $this->id?->toInt(),
            'review_article_id' => $this->articleId->__toString(),
            'review_playable_id' => $this->playableId->__toString(),
            'review_rating' => $this->rating->toFloat(),
            'review_body' => $this->body,
            'review_cons' => $this->cons,
            'review_pros' => $this->pros,
        ];
    }
}