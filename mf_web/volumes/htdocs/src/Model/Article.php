<?php

namespace MF\Model;

use DateTimeImmutable;

class Article implements Entity
{
    private Slug $id;

    private LongString $authorUsername;

    private Category|Slug $category;

    private string $content;

    private bool $isFeatured;

    private LongString $title;

    private ?SlugFilename $coverFilename;

    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastUpdateDateTime;

    private ?Uint $reviewId;

    public function __construct(
        Slug $id,
        LongString $authorUsername,
        Slug|Category $category,
        string $content,
        bool $isFeatured,
        LongString $title,
        ?SlugFilename $coverFilename = null,
        DateTimeImmutable $creationDateTime = new DateTimeImmutable(),
        DateTimeImmutable $lastUpdateDateTime = new DateTimeImmutable(),
        ?Uint $reviewId = null,
    ) {
        $this->id = $id;
        $this->authorUsername = $authorUsername;
        $this->category = $category;
        $this->content = $content;
        $this->isFeatured = $isFeatured;
        $this->title = $title;
        $this->coverFilename = $coverFilename;
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
        $this->lastUpdateDateTime = $lastUpdateDateTime ?? new DateTimeImmutable();
        $this->reviewId = $reviewId;
    }

    public static function fromArray(array $data): self {
        return new self(
            new Slug($data['p_id']),
            new LongString($data['p_author_username']),
            null !== $data['p_category_name'] ? new Category(new Slug($data['p_category_id']), new LongString($data['p_category_name'])) : new Slug($data['p_category_id']),
            $data['p_content'],
            $data['p_is_featured'],
            new LongString($data['p_title']),
            $data['p_cover_filename'] !== null ? new SlugFileName($data['p_cover_filename']) : null,
            new DateTimeImmutable($data['p_creation_datetime']),
            new DateTimeImmutable($data['p_last_update_datetime']),
            is_string($data['p_review_id']) ? intval($data['p_review_id']) : null,
        );
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getAuthorUsername(): string {
        return $this->authorUsername;
    }

    public function getCategory(): ?Category {
        return $this->category instanceof Category ? $this->category : null;
    }

    public function getCategoryId(): Slug {
        return $this->category instanceof Category ? $this->category->getId() : $this->category;
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

    public function getCreationDateTime(): DateTimeImmutable {
        return $this->creationDateTime;
    }

    public function getLastUpdateDateTime(): DateTimeImmutable {
        return $this->lastUpdateDateTime;
    }

    public function getReviewId(): ?int {
        return $this->reviewId?->toInt();
    }

    public function isFeatured(): bool {
        return $this->isFeatured;
    }

    public function isReview(): bool {
        return null !== $this->reviewId;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_author_username' => $this->authorUsername->__toString(),
            'p_category_id' => $this->category->__toString(),
            'p_category_name' => $this->category instanceof Category ? $this->category->getName() : null,
            'p_content' => $this->content,
            'p_is_featured' => $this->isFeatured,
            'p_title' => $this->title->__toString(),
            'p_cover_filename' => $this->coverFilename?->__toString(),
            'p_creation_datetime' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'p_last_update_datetime' => $this->lastUpdateDateTime->format('Y-m-d H:m:s'),
            'p_review_id' => $this->reviewId?->toInt(),
        ];
    }
}