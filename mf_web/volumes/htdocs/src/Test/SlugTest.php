<?php

namespace MF\Test;

use MF\Framework\Test\IUnitTest;
use MF\Model\Slug;

class SlugTest implements IUnitTest
{
    public function run(): array {
        $tester = new Tester();

        $tester->assertEquals('mise-a-jour-1-5-pour-the-crystal-mission',
            (new Slug('Mise à jour 1.5 pour The Crystal Mission', true))->__toString()
        );

        return $tester->getErrors();
    }
}