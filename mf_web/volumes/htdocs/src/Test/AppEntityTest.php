<?php

namespace MF\Test;

use InvalidArgumentException;
use MF\Framework\Type\ModelValidator;
use MF\Framework\Test\IUnitTest;
use MF\Model\AuthorModelFactory;

class AppEntityTest implements IUnitTest
{
    public function __construct(
        private ModelValidator $modelValidator,
        private AuthorModelFactory $authorModelFactory,
    ) {
    }

    public function run(): array {
        $tester = new Tester();
        $modelValidator = $this->modelValidator;
        $model = $this->authorModelFactory->create();

        $tester->assertEquals(0, count($modelValidator->validate([
                'id' => 'a',
                'name' => 'The Author',
            ],
            $model,
        )));

        $tester->assertEquals(1, count($modelValidator->validate([
                'id' => 'a',
                'name' => 'The Author',
                'extra' => 'The Author',
            ],
            $model,
        )));

        $tester->assertEquals(2, count($modelValidator->validate([
                'id' => 'I do not match the regex',
                'name' => '',
            ],
            $model,
        )));

        $tester->assertException(
            InvalidArgumentException::class,
            function () use ($model, $modelValidator) {
                $modelValidator->validate('', $model);
            },
        );

        $tester->assertException(
            InvalidArgumentException::class,
            function () use ($model, $modelValidator) {
                $modelValidator->validate([
                    'id' => null,
                    'name' => 'The Author',
                ], $model);
            },
        );

        return $tester->getErrors();
    }
}