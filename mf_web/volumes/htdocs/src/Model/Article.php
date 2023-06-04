<?php

namespace MF\Model;

use DateTimeImmutable;

class Article implements Entity
{
    private Slug $id;

    private LongString $authorUsername;

    private string $content;

    private bool $isFeatured;

    private LongString $title;

    private ?SlugFilename $coverFilename;

    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastUpdateDateTime;

    private ?int $reviewId;

    public function __construct(
        string $id,
        string $authorUsername,
        string $content,
        bool $isFeatured,
        string $title,
        ?SlugFilename $coverFilename = null,
        ?DateTimeImmutable $creationDateTime = null,
        ?DateTimeImmutable $lastUpdateDateTime = null,
        ?int $reviewId = null,
    ) {
        $this->id = new Slug($id);
        $this->authorUsername = new LongString($authorUsername);
        $this->content = $content;
        $this->isFeatured = $isFeatured;
        $this->title = new LongString($title);
        $this->coverFilename = $coverFilename ?? null;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
        $this->lastUpdateDateTime = $lastUpdateDateTime ?? new DateTimeImmutable();
        $this->reviewId = $reviewId;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['p_id'],
            $data['p_author_username'],
            $data['p_content'],
            $data['p_is_featured'],
            $data['p_title'],
            isset($data['p_cover_filename']) ? new SlugFileName($data['p_cover_filename']) : null,
            new DateTimeImmutable($data['p_creation_datetime']),
            new DateTimeImmutable($data['p_last_update_datetime']),
            $data['review_id']
        );
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getAuthorUsername(): string {
        return $this->authorUsername;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getTitle(): LongString {
        return $this->title;
    }

    public function getCoverFilename(): ?string {
        return $this->coverFilename?->__toString();
    }

    public function getReviewId(): ?int {
        return $this->reviewId;
    }

    public function isFeatured(): bool {
        return $this->isFeatured;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_author_username' => $this->authorUsername->__toString(),
            'p_content' => $this->content,
            'p_is_featured' => $this->isFeatured,
            'p_title' => $this->title->__toString(),
            'p_cover_filename' => $this->coverFilename?->__toString(),
            'p_creation_datetime' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'p_last_update_datetime' => $this->lastUpdateDateTime->format('Y-m-d H:m:s'),
            'p_review_id' => $this->reviewId,
        ];
    }
}