<?php

namespace MF\Framework\Model;

interface IEntity extends IModel
{
    /**
     * @return \MF\Framework\Model\IModel;
     */
    public function getProperties(): array;
}