<?php

use MF\DataStructure\AppObject;
use MF\Enum\LinkType;
use MF\Exception\Entity\EntityValidationException;
use MF\Model\ContributionModel;
use MF\Model\PlayableModel;
use MF\Model\PlayableLinkModel;
use MF\Test\Tester;

$container = require_once(dirname(__FILE__) . '/../index.php');

$tester = $container->get(Tester::class);

$goodData = [
    'id' => 0,
    'author_id' => 'root',
    'playable_id' => 'sven-co-op',
    'is_author' => true,
    'summary' => null,
];

$constraint = new ContributionModel();
$entity = new AppObject($goodData, $constraint);

$tester->assertEquals($entity->id, $goodData['id']);
$tester->assertEquals($entity->authorId, $goodData['author_id']);
$tester->assertEquals($entity->playableId, $goodData['playable_id']);
$tester->assertEquals($entity->isAuthor, $goodData['is_author']);
$tester->assertEquals($entity->summary, $goodData['summary']);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        array_diff($goodData, ['sven-co-op']),
        $constraint,
    ),
);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        ['id' => 'hello'] + $goodData,
        $constraint,
    ),
);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        ['author_id' => ''] + $goodData,
        $constraint,
    ),
);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        ['playable_id' => 'sven_co_op'] + $goodData,
        $constraint,
    ),
);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        ['author_id' => 'another_author'] + $goodData,
        $constraint,
    ),
);

$tester->assertException(
    EntityValidationException::class,
    fn() => new AppObject(
        ['playable_id' => null] + $goodData,
        $constraint,
    ),
);

$tester->assertNoException(
    fn() => new AppObject(
        $goodData,
        $constraint,
    ),
);

$playableDef = new PlayableModel(gameDef: new PlayableModel(), playableLinkDef: new PlayableLinkModel());

$playableData = [
    'id' => 'un-jeu',
    'name' => 'THE GAME',
    'release_date_time' => new DateTimeImmutable(),
    'game_id' => 'my-game',
    'stored_game' => [
        'id' => 'my-game',
        'name' => 'ANOTHER GAME',
        'release_date_time' => new DateTimeImmutable(),
        'game_id' => null,
    ],
    'stored_links' => [
        [
        'id' => null,
        'playable_id' => 'un-jeu',
        'name' => 'Un lien',
        'type' => LinkType::Other->value,
        'url' => 'http://http.com',
        ],
    ],
];

$tester->assertNoException(
    fn() => new AppObject(
        $playableData,
        $playableDef,
    ),
);

if (count($tester->getErrors()) > 0) {
    var_dump($tester->getErrors());
    exit(1);
} else {
    echo "All the tests passed.\n";
    exit(0);
}