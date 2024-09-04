<?php

namespace MF\Test;

interface IUnitTest
{
    /**
     * @return AssertionFailure[]
     */
    public function run(): array;
}