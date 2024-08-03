<?php

namespace MF\Test;

use InvalidArgumentException;
use LM\WebFramework\Validator\ModelValidator;
use LM\WebFramework\Test\IUnitTest;
use MF\Model\AuthorModelFactory;

class AppEntityTest implements IUnitTest
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
    ) {  
    }

    public function run(): array {
        $tester = new Tester();
        $model = $this->authorModelFactory->create();
        $modelValidator = new ModelValidator($model);

        $tester->assertEquals(0, count($modelValidator->validate([
                'id' => 'a',
                'name' => 'The Author',
                'avatar_filename' => null,
            ],
            $model,
        )));

        $tester->assertEquals(1, count($modelValidator->validate([
                'id' => null,
                'name' => 'The Author',
                'avatar_filename' => null,
            ],
            $model,
        )));

        $tester->assertException(
            InvalidArgumentException::class,
            function () use ($model, $modelValidator) {
                $modelValidator->validate([
                    'id' => 'a',
                    'name' => 'The Author',
                    'avatar_filename' => null,
                    'extra' => 'The Author',
                ], $model);
            },
        );

        $tester->assertEquals(2, count($modelValidator->validate([
                'id' => 'I do not match the regex',
                'name' => '',
                'avatar_filename' => null,
            ],
            $model,
        )));

        $tester->assertException(
            InvalidArgumentException::class,
            function () use ($model, $modelValidator) {
                $modelValidator->validate('', $model);
            },
        );

        return $tester->getErrors();
    }
}