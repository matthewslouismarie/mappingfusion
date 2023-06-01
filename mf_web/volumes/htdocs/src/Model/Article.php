<?php

namespace MF\Model;
use DateTimeImmutable;

class Article
{
    private LongString $id;

    private DateTimeImmutable $creationDateTime;

    private bool $isFeatured;

    private DateTimeImmutable $lastModificationDateTime;

    private LongString $authorUsername;

    private LongString $title;

    private string $content;

    private ?SlugFilename $coverFilename;

    public function __construct(
        string $id,
        DateTimeImmutable $creationDateTime,
        bool $isFeatured,
        DateTimeImmutable $lastModificationDateTime,
        string $authorUsername,
        string $content,
        string $title,
        ?string $coverFilename,
    ) {
        $this->id = new LongString($id);
        $this->creationDateTime = $creationDateTime;
        $this->isFeatured = $isFeatured;
        $this->lastModificationDateTime = $lastModificationDateTime;
        $this->authorUsername = new LongString($authorUsername);
        $this->title = new LongString($title);
        $this->content = $content;
        $this->coverFilename = null !== $coverFilename ? new SlugFilename($coverFilename) : null;
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['p_id'],
            new DateTimeImmutable($data['p_creation_datetime']),
            $data['p_is_featured'],
            new DateTimeImmutable($data['p_last_update_datetime']),
            $data['p_author'],
            $data['p_content'],
            $data['p_title'],
            isset($data['p_cover_filename']) ? $data['p_cover_filename'] : null,
        );
    }

    public function getId(): string {
        return $this->id;
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

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_creation_datetime' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'p_is_featured' => $this->isFeatured,
            'p_last_update_datetime' => $this->lastModificationDateTime->format('Y-m-d H:m:s'),
            'p_author' => $this->authorUsername->__toString(),
            'p_title' => $this->title->__toString(),
            'p_content' => $this->content,
            'p_cover_filename' => $this->coverFilename?->__toString(),
        ];
    }
}