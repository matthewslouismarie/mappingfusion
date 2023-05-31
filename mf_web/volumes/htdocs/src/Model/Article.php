<?php

namespace MF\Model;
use DateTimeImmutable;

class Article
{
    private LongString $id;

    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastModificationDateTime;

    private LongString $authorUsername;

    private LongString $title;

    private string $content;

    public function __construct(
        string $id,
        DateTimeImmutable $creationDateTime,
        DateTimeImmutable $lastModificationDateTime,
        string $authorUsername,
        string $content,
        string $title,
    ) {
        $this->id = new LongString($id);
        $this->creationDateTime = $creationDateTime;
        $this->lastModificationDateTime = $lastModificationDateTime;
        $this->authorUsername = new LongString($authorUsername);
        $this->title = new LongString($title);
        $this->content = $content;
    }

    public static function fromArray(array $data) {
        return new self(
            $data['p_id'],
            new DateTimeImmutable($data['p_creation_datetime']),
            new DateTimeImmutable($data['p_last_update_datetime']),
            $data['p_author'],
            $data['p_content'],
            $data['p_title'],
        );
    }

    public function getTitle(): LongString {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function toArray(): array {
        return [
            'p_id' => $this->id->__toString(),
            'p_creation_datetime' => $this->creationDateTime->format('Y-m-d H:m:s'),
            'p_last_update_datetime' => $this->lastModificationDateTime->format('Y-m-d H:m:s'),
            'p_author' => $this->authorUsername->__toString(),
            'p_title' => $this->title->__toString(),
            'p_content' => $this->content,
        ];
    }
}