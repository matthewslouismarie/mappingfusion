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
            new Slug($data['article_id']),
            new LongString($data['article_author_id']),
            null !== $data['p_category_name'] ? new Category(new Slug($data['article_category_id']), new LongString($data['p_category_name'])) : new Slug($data['article_category_id']),
            $data['article_body'],
            $data['article_is_featured'],
            new LongString($data['article_title']),
            $data['article_cover_filename'] !== null ? new SlugFileName($data['article_cover_filename']) : null,
            new DateTimeImmutable($data['article_creation_date_time']),
            new DateTimeImmutable($data['article_last_update_date_time']),
            null !== $data['article_review_id'] ? new Uint($data['article_review_id']) : null,
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
            'article_id' => $this->id->__toString(),
            'article_author_id' => $this->authorUsername->__toString(),
            'article_category_id' => $this->category->__toString(),
            'p_category_name' => $this->category instanceof Category ? $this->category->getName() : null,
            'article_body' => $this->content,
            'article_is_featured' => $this->isFeatured,
            'article_title' => $this->title->__toString(),
            'article_cover_filename' => $this->coverFilename?->__toString(),
            'article_creation_date_time' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'article_last_update_date_time' => $this->lastUpdateDateTime->format('Y-m-d H:m:s'),
            'article_review_id' => $this->reviewId?->toInt(),
        ];
    }
}