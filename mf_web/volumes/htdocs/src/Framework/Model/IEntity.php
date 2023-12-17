<?php

namespace MF\Framework\Model;

interface IEntity
{
    /**
     * @return \MF\Framework\Model\IModel[]
     */
    public function getProperties(): array;
}