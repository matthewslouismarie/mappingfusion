<?php

namespace MF\Model;

use DateTimeImmutable;
use OutOfBoundsException;
use TypeError;

class Article implements Entity
{
    private Slug $id;

    private LongString $authorUsername;

    private Slug $categoryId;

    private string $content;

    private bool $isFeatured;

    private LongString $title;

    private ?SlugFilename $coverFilename;

    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastUpdateDateTime;

    private ?Uint $reviewId;

    private ?Category $storedCategory;

    private ?Review $storedReview;

    public function __construct(
        ?string $id,
        string $authorUsername,
        string $categoryId,
        string $content,
        bool $isFeatured,
        string $title,
        ?string $coverFilename = null,
        DateTimeImmutable $creationDateTime = new DateTimeImmutable(),
        DateTimeImmutable $lastUpdateDateTime = new DateTimeImmutable(),
        ?int $reviewId = null,
        ?Category $storedCategory = null,
        ?Review $storedReview = null,
    ) {
        $this->id = null !== $id ? new Slug($id) : new Slug($title, true);
        $this->authorUsername = new LongString($authorUsername);
        $this->categoryId = new Slug($categoryId);
        $this->content = $content;
        $this->isFeatured = $isFeatured;
        $this->title = new LongString($title);
        $this->coverFilename = new SlugFilename($coverFilename);
        $this->creationDateTime = $creationDateTime ?? new DateTimeImmutable();
        $this->lastUpdateDateTime = $lastUpdateDateTime ?? new DateTimeImmutable();
        $this->reviewId = $reviewId !== null ? new Uint($reviewId) : null;
        $this->storedCategory = $storedCategory;
        $this->storedReview = $storedReview;
    }

    // @todo Use prefix.
    public static function fromArray(array $data): self {
        $review = isset($data['review_id']) ? Review::fromArray($data) : null;

        $category = isset($data['category_id']) ? Category::fromArray($data) : null;

        return new self(
            $data['article_id'],
            $data['article_author_id'],
            $data['article_category_id'],
            $data['article_body'],
            $data['article_is_featured'],
            $data['article_title'],
            $data['article_cover_filename'],
            new DateTimeImmutable($data['article_creation_date_time']),
            new DateTimeImmutable($data['article_last_update_date_time']),
            $data['article_review_id'],
            $category,
            $review,
        );
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getAuthorUsername(): string {
        return $this->authorUsername;
    }

    public function getCategory(): ?Category {
        return $this->storedCategory;
    }

    public function getCategoryId(): Slug {
        return $this->categoryId;
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

    public function getStoredReview(): ?Review {
        return $this->storedReview;
    }

    public function isFeatured(): bool {
        return $this->isFeatured;
    }

    public function isReview(): bool {
        return null !== $this->reviewId;
    }

    public function toArray(): array {
        $category = $this->storedCategory?->toArray() ?? [];
        $review = $this->storedReview?->toArray() ?? [];
        return $category + $review + [
            'article_id' => $this->id->__toString(),
            'article_author_id' => $this->authorUsername->__toString(),
            'article_category_id' => $this->categoryId->__toString(),
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