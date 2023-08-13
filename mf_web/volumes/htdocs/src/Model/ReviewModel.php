<?php

namespace MF\Model;

use MF\Constraint\DecimalConstraint;
use MF\Constraint\IModel;
use MF\Constraint\SlugConstraint;
use MF\Constraint\TextConstraint;
use MF\Constraint\UintConstraint;

class ReviewModel implements IModel
{
    public function __construct(
        private ?PlayableModel $playableModel = null,
    ) {
    }

    public function getName(): string {
        return 'review';
    }

    public function getProperties(): array {
        $properties = [
            new ModelProperty('id', new UintConstraint(), isGenerated: true, isRequired: false),
            new ModelProperty('article_id', new SlugConstraint()),
            new ModelProperty('playable_id', new SlugConstraint()),
            new ModelProperty('rating', new DecimalConstraint(max: 5, min: 1)),
            new ModelProperty('body', new TextConstraint()),
            new ModelProperty('cons', new TextConstraint()),
            new ModelProperty('pros', new TextConstraint()),
        ];
        if (null !== $this->playableModel) {
            $properties[] = new ModelProperty('playable', $this->playableModel, isRequired: false);
        }
        return $properties;
    }
}