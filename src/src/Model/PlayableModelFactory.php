<?php

namespace MF\Model;

use LM\WebFramework\Model\Constraints\EnumConstraint;
use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Type\DateTimeModel;
use LM\WebFramework\Model\Type\EntityListModel;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\ForeignEntityModel;
use LM\WebFramework\Model\Type\StringModel;
use MF\Enum\PlayableType;

/**
 * @todo Add minimum length to playable name. (and other constraints?)
 */
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
        ?EntityModel $modModel = null,
        bool $isGame = false,
        bool $isNullable = false,
    ): EntityModel {
        $properties = [
            'id' => $this->slugModelFactory->getSlugModel(),
            'name' => new StringModel(),
        ];
        if (!$isGame) {
            $properties += [
                'release_date_time' => new DateTimeModel(),
                'game_id' => $this->slugModelFactory->getSlugModel(isNullable: true),
                'type' => new StringModel(enumConstraint: new EnumConstraint(PlayableType::cases())),
            ];
        }
        if (null !== $gameModel) {
            $properties['game'] = new ForeignEntityModel($gameModel, 'id', 'game_id', isNullable: true);
        }
        if (null !== $playableLinkModel) {
            $properties['links'] = new EntityListModel(
                new ForeignEntityModel($playableLinkModel, 'playable_id', 'id')
            );
        }
        if (null !== $contributionModel) {
            $properties['contributions'] = new EntityListModel(
                new ForeignEntityModel($contributionModel, 'playable_id', 'id'),
            );
        }
        if (null !== $modModel) {
            $properties['mods'] = new EntityListModel(
                new ForeignEntityModel($modModel, 'game_id', 'id'),
            );
        }
        
        return new EntityModel(
            $isGame ? 'game' : 'playable',
            $properties,
            isNullable: $isNullable,
        );
    }
}