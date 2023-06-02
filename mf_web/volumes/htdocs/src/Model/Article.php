<?php

namespace MF\Model;

use DateTimeImmutable;

class Article implements Entity
{
    private Slug $id;

    private LongString $authorUsername;

    private string $content;

    private bool $isFeatured;

    private bool $isReview;

    private LongString $title;

    private ?SlugFilename $coverFilename;

    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastUpdateDateTime;

    public function __construct(
        string $id,
        string $authorUsername,
        string $content,
        bool $isFeatured,
        bool $isReview,
        string $title,
        ?SlugFilename $coverFilename = null,
        ?DateTimeImmutable $creationDateTime = null,
        ?DateTimeImmutable $lastUpdateDateTime = null,
    ) {
        $this->id = new Slug($id);
        $this->authorUsername = new LongString($authorUsername);
        $this->content = $content;
        $this->isFeatured = $isFeatured;
        $this->isReview = $isReview;
        $this->title = new LongString($title);
        $this->coverFilename = $coverFilename ?? null;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
        $this->lastUpdateDateTime = $lastUpdateDateTime ?? new DateTimeImmutable();
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['p_id'],
            $data['p_author_username'],
            $data['p_content'],
            $data['p_is_featured'],
            $data['p_is_review'],
            $data['p_title'],
            isset($data['p_cover_filename']) ? new SlugFileName($data['p_cover_filename']) : null,
            new DateTimeImmutable($data['p_creation_datetime']),
            new DateTimeImmutable($data['p_last_update_datetime']),
        );
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getAuthorUsername(): string {
        return $this->authorUsername;
    }

    public function getTitle(): LongString {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getCoverFilename(): ?string {
        return $this->coverFilename?->__toString();
    }

    public function isFeatured(): bool {
        return $this->isFeatured;
    }

    public function isReview(): bool {
        return $this->isReview;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_author_username' => $this->authorUsername->__toString(),
            'p_content' => $this->content,
            'p_is_featured' => $this->isFeatured,
            'p_is_review' => $this->isReview,
            'p_title' => $this->title->__toString(),
            'p_cover_filename' => $this->coverFilename?->__toString(),
            'p_creation_datetime' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'p_last_update_datetime' => $this->lastUpdateDateTime->format('Y-m-d H:m:s'),
        ];
    }
}