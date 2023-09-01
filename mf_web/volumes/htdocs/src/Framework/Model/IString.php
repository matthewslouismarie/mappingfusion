<?php

namespace MF\Framework\Model;

interface IString
{
    /**
     * @return \MF\Framework\Constraints\IConstraint[]
     */
    public function getStringConstraints(): array;
}