<?php

namespace MF\Form;
use MF\Model\PlayableLink;

class PlayableLinkTransformer
{
    public function fromForm(array $formData): PlayableLink
    {
        return PlayableLink::fromArray($formData);
    }
}