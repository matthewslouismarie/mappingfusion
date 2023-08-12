<?php

namespace MF\Model;

use MF\Constraint\ArrayConstraint;
use MF\Constraint\IDateTimeConstraint;
use MF\Constraint\IModel;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;

class PlayableModel implements IModel
{
    public function __construct(
        private ?self $gameModel = null,
        private ?PlayableLinkModel $playableLinkModel = null,
    ) {
    }

    public function getProperties(): array {
        $properties = [
            new ModelProperty('id', new SlugConstraint()),
            new ModelProperty('name', new LongStringConstraint()),
            new ModelProperty('release_date_time', new class implements IDateTimeConstraint {}),
            new ModelProperty('game_id', new SlugConstraint(), isRequired: false),
        ];
        if (null !== $this->gameModel) {
            $properties[] = new ModelProperty('stored_game', $this->gameModel, isPersisted: false);
        }
        if (null !== $this->playableLinkModel) {
            $properties[] = new ModelProperty('stored_links', new ArrayConstraint($this->playableLinkModel), isPersisted: false);
        }
        return $properties;
    }
}