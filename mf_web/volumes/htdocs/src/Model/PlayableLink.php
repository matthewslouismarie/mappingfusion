<?php

namespace MF\Model;
use MF\Enum\LinkType;

class PlayableLink implements EntityInterface
{
    private ?Uint $id;

    private Slug $playableId;

    private LongString $name;

    private LinkType $type;

    private Url $url;

    public static function fromArray(array $data, string $prefix = 'link_'): self {
            return new self(
            $data["{$prefix}id"] ?? null,
            $data["{$prefix}playable_id"],
            $data["{$prefix}name"],
            LinkType::fromString($data["{$prefix}type"]),
            $data["{$prefix}url"],
        );
    }

    public function __construct(
        ?int $id,
        string $playableId,
        string $name,
        LinkType $type,
        string $url,
    ) {
        $this->id = null !== $id ? new Uint($id) : null;
        $this->playableId = new Slug($playableId);
        $this->name = new LongString($name);
        $this->type = $type;
        $this->url = new Url($url);
    }

    public function getId(): ?int {
        return $this->id?->toInt();
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function toArray(string $prefix = 'link_'): array {
        return [
            "{$prefix}id" => $this->id?->toInt(),
            "{$prefix}playable_id" => $this->playableId->__toString(),
            "{$prefix}name" => $this->name->__toString(),
            "{$prefix}type" => $this->type->value,
            "{$prefix}url" => $this->url->__toString(),
        ];
    }
}