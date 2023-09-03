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
        $properties = [
            'id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'name' => new StringModel([new StringConstraint()]),
            'release_date_time' => new DateTimeModel(),
            'game_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)], true),
        ];
        if (null !== $gameModel) {
            $properties['game'] = $gameModel;
        }
        if (null !== $playableLinkModel) {
            $properties['links'] = new ListModel($playableLinkModel);
        }
        if (null !== $contributionModel) {
            $properties['contributions'] = new ListModel($contributionModel);
        }
        parent::__construct($properties);
    }
}