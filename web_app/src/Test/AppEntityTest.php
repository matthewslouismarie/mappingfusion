<?php

namespace MF\Test;

use LM\WebFramework\Validation\Validator;
use MF\Model\AuthorModelFactory;

class AppEntityTest implements IUnitTest
{
    public function __construct(
        private AuthorModelFactory $authorModelFactory,
    ) {  
    }

    public function run(): array
    {
        $tester = new Tester();
        $model = $this->authorModelFactory->create();
        $modelValidator = new Validator($model);

        $tester->assertArraySize(
            $modelValidator->validate(
                [
                'id' => 'a',
                'name' => 'The Author',
                'avatar_filename' => null,
                ]
            ),
            0,
        );

        $tester->assertArraySize(
            $modelValidator->validate(
                [
                'id' => null,
                'name' => 'The Author',
                'avatar_filename' => null,
                ]
            ),
            1,
        );

        $tester->assertArraySize(
            $modelValidator->validate(
                [
                'id' => 'a',
                'name' => 'The Author',
                'avatar_filename' => null,
                'extra' => 'The Author',
                ]
            ),
            0,
        );

        $tester->assertArraySize(
            $modelValidator->validate(
                [
                'id' => 'a',
                'name' => 'The Author',
                ]
            ),
            1,
        );

        $tester->assertArraySize(
            $modelValidator->validate(
                [
                'id' => 'I do not match the regex',
                'name' => '',
                'avatar_filename' => null,
                ]
            ),
            2,
        );

        $tester->assertArraySize(
            $modelValidator->validate(''),
            1,
        );

        return $tester->getErrors();
    }
}