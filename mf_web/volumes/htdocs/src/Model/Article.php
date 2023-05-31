<?php

namespace MF\Model;
use DateTimeImmutable;

class Article
{
    private DateTimeImmutable $creationDateTime;

    private DateTimeImmutable $lastModificationDateTime;

    private LongString $authorUsername;

    private LongString $title;

    private string $content;

    public function __construct(
        DateTimeImmutable $creationDateTime,
        DateTimeImmutable $lastModificationDateTime,
        string $authorUsername,
        string $title,
        string $content,
    ) {
        $this->creationDateTime = $creationDateTime;
        $this->lastModificationDateTime = $lastModificationDateTime;
        $this->authorUsername = new LongString($authorUsername);
        $this->title = new LongString($title);
        $this->content = $content;
    }
}