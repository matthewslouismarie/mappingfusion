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
        private ?ContributionModel $contributionModel = null,
    ) {
    }
    
    public function getName(): string {
        return 'playable';
    }

    public function getProperties(): array {
        $properties = [
            new ModelProperty('id', new SlugConstraint()),
            new ModelProperty('name', new LongStringConstraint()),
            new ModelProperty('release_date_time', new class implements IDateTimeConstraint {}),
            new ModelProperty('game_id', new SlugConstraint(), isRequired: false),
        ];
        if (null !== $this->gameModel) {
            $properties[] = new ModelProperty('game', $this->gameModel);
        }
        if (null !== $this->playableLinkModel) {
            $properties[] = new ModelProperty('links', new ArrayConstraint($this->playableLinkModel));
        }
        if (null !== $this->contributionModel) {
            $properties[] = new ModelProperty('contributions', new ArrayConstraint($this->contributionModel));
        }
        return $properties;
    }
}