<?php

namespace MF\Model;

use MF\Framework\Constraints\StringConstraint;
use MF\Framework\Model\AbstractEntity;
use MF\Framework\Model\DateTimeModel;
use MF\Framework\Model\ListModel;
use MF\Framework\Model\StringModel;

class PlayableModel extends AbstractEntity
{
    public function __construct(
        private ?self $gameModel = null,
        private ?PlayableLinkModel $playableLinkModel = null,
        private ?ContributionModel $contributionModel = null,
    ) {
    }

    public function getArrayDefinition(): array {
        $properties = [
            'id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'name' => new StringModel([new StringConstraint()]),
            'release_date_time' => new DateTimeModel(),
            'game_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)], true),
        ];
        if (null !== $this->gameModel) {
            $properties['game'] = $this->gameModel;
        }
        if (null !== $this->playableLinkModel) {
            $properties['links'] = new ListModel($this->playableLinkModel);
        }
        if (null !== $this->contributionModel) {
            $properties['contributions'] = new ListModel($this->contributionModel);
        }
        return $properties;
    }
}