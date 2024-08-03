<?php

namespace MF\Model;

use LM\WebFramework\Model\Constraints\EnumConstraint;
use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ListModel;
use LM\WebFramework\Model\Type\StringModel;
use MF\Enum\PlayableType;

class PlayableModelFactory
{
    public function __construct(
        private SlugModelFactory $slugModelFactory,
    ) {
    }

    public function create(
        ?EntityModel $gameModel = null,
        ?EntityModel $playableLinkModel = null,
        ?EntityModel $contributionModel = null,
        bool $isNullable = false,
    ): EntityModel {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'name' => new StringModel(),
            'release_date_time' => new DateTimeModel(),
            'game_id' => $this->slugModelFactory->getSlugModel(isNullable: true),
            'type' => new StringModel(enumConstraint: new EnumConstraint(PlayableType::cases())),
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
        
        return new EntityModel(
            'playable',
            $properties,
            'id',
            $isNullable,
        );
    }
}