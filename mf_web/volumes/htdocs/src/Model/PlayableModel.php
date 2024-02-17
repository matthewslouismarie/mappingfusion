<?php

namespace MF\Model;

use LM\WebFramework\Constraints\EnumConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\DateTimeModel;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\StringModel;
use MF\Enum\PlayableType;

class PlayableModel extends AbstractEntity
{
    public function __construct(
        private ?self $gameModel = null,
        private ?PlayableLinkModel $playableLinkModel = null,
        private ?ContributionModel $contributionModel = null,
        private bool $isNullable = false,
    ) {
        $properties = [
            'id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)]),
            'name' => new StringModel([new StringConstraint()]),
            'release_date_time' => new DateTimeModel(),
            'game_id' => new StringModel([new StringConstraint(regex: StringConstraint::REGEX_DASHES)], true),
            'type' => new StringModel([new EnumConstraint(PlayableType::cases())]),
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
        parent::__construct($properties, $isNullable);
    }
}