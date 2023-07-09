<?php

namespace MF\Model;
use DateTimeImmutable;
use DomainException;
use MF\Exception\InvalidEntityArrayException;
use OutOfBoundsException;
use TypeError;

// @todo Separate date and time for release? In case time is unknown.
class Playable implements Entity
{
    private Slug $id;

    private LongString $name;

    private DateTimeImmutable $releaseDateTime;

    private ?Slug $gameId;

    private ?array $storedAuthors;

    private ?Playable $storedGame;

    private ?array $storedLinks;

    /**
     * @todo Stored game should be read from stored_game prefix or something like that.
     */
    static function fromArray(array $data, string $prefix = 'playable_', string $linkPrefix = 'link_'): self {
        $gameId = $data["{$prefix}game_id"] ?? null;
        try {
            $game = null !== $gameId ? Playable::fromArray($data, "{$prefix}game_") : null;
        } catch (InvalidEntityArrayException $e) {
            $game = null;
        }

        $links = null;
        if (isset($data["{$prefix}stored_links"]) && null !== $data["{$prefix}stored_links"]) {
            $links = [];
            foreach ($data["{$prefix}stored_links"] as $linkData) {
                $links[] = PlayableLink::fromArray($linkData, $linkPrefix);
            }
        }

        try {
            return new self(
                $data["{$prefix}id"],
                $data["{$prefix}name"],
                new DateTimeImmutable($data["{$prefix}release_date_time"]),
                $data["{$prefix}game_id"],
                null,
                $game,
                $links,
            );
        } catch (DomainException|OutOfBoundsException|TypeError $e) {
            throw new InvalidEntityArrayException(Playable::class, $e);
        }
    }

    public function __construct(
        ?string $id,
        string $name,
        DateTimeImmutable $releaseDateTime,
        ?string $gameId,
        ?array $storedAuthors = null,
        ?Playable $storedGame = null,
        ?array $storedLinks = null,
    ) {
        $this->id = null !== $id ? new Slug($id) : new Slug($name, true);
        $this->name = new LongString($name);
        $this->releaseDateTime = $releaseDateTime;
        $this->gameId = null !== $gameId ? new Slug($gameId) : null;
        $this->storedAuthors = $storedAuthors;
        $this->storedGame = $storedGame;
        $this->storedLinks = $storedLinks;
    }

    public function getId(): string {
        return $this->id->__toString();
    }

    public function getName(): string {
        return $this->name->__toString();
    }

    public function getReleaseDateTime(): DateTimeImmutable {
        return $this->releaseDateTime;
    }

    public function getStoredAuthors(): ?array {
        return $this->storedAuthors;
    }

    public function getStoredGame(): ?Playable {
        return $this->storedGame;
    }

    public function getStoredLinks(): ?array {
        return $this->storedLinks;
    }

    public function toArray(string $prefix = 'playable_', ?string $linkPrefix = null): array {
        $links = null;
        if (null !== $this->storedLinks) {
            $links = [];
            foreach ($this->storedLinks as $link) {
                $links[] = null !== $linkPrefix ? $link->toArray($linkPrefix) : $link->toArray();
            }
        }

        return [
            "{$prefix}id" => $this->id->__toString(),
            "{$prefix}name" => $this->name->__toString(),
            "{$prefix}game_id" => $this->gameId?->__toString(),
            "{$prefix}release_date_time" => $this->releaseDateTime->format('Y-m-d H:m:s'),
            "{$prefix}stored_links" => $links,
        ];
    }
}