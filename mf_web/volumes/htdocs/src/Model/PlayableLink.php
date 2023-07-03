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

    public static function fromArray(array $data, string $prefix = 'link'): self {
        return new self(
            $data["{$prefix}_id"],
            $data["{$prefix}_playable_id"],
            $data["{$prefix}_name"],
            LinkType::fromString($data["{$prefix}_type"]),
            $data["{$prefix}_url"],
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

    public function toArray(string $prefix = 'link'): array {
        return [
            "{$prefix}_id" => $this->id?->toInt(),
            "{$prefix}_playable_id" => $this->playableId->__toString(),
            "{$prefix}_name" => $this->name->__toString(),
            "{$prefix}_type" => $this->type->value,
            "{$prefix}_url" => $this->url->__toString(),
        ];
    }
}