<?php

use MF\Constraint\DecimalConstraint;
use MF\Database\DbEntityManager;
use MF\DataStructure\DecimalNumber;
use MF\Test\Tester;
use MF\Validator\DecimalNumberValidator;

$container = require_once(dirname(__FILE__) . '/../index.php');

$tester = $container->get(Tester::class);

$number1 = new DecimalNumber(40);
$tester->assertEquals(40, $number1->getNumerator());
$tester->assertEquals(0, $number1->getDecimalPower());
$tester->assertEquals('40', $number1->getIntegralPart());
$tester->assertEquals('', $number1->getDecimalPart());

$number1 = new DecimalNumber('0.18');
$tester->assertEquals(18, $number1->getNumerator());
$tester->assertEquals(2, $number1->getDecimalPower());
$tester->assertEquals('0', $number1->getIntegralPart());
$tester->assertEquals('18', $number1->getDecimalPart());

$number1 = new DecimalNumber('33.1');
$tester->assertEquals(331, $number1->getNumerator());
$tester->assertEquals(1, $number1->getDecimalPower());
$tester->assertEquals('33', $number1->getIntegralPart());
$tester->assertEquals('1', $number1->getDecimalPart());

$number1 = new DecimalNumber('010.00180');
$tester->assertEquals(1000180, $number1->getNumerator());
$tester->assertEquals(5, $number1->getDecimalPower());
$tester->assertEquals('10', $number1->getIntegralPart());
$tester->assertEquals('00180', $number1->getDecimalPart());
$tester->assertEquals(10.00180, $number1->toFloat());

$validator = new DecimalNumberValidator(new DecimalConstraint(5, 1, 0));

$tester->assertEquals(1, count($validator->validate(50)));
$tester->assertEquals(0, count($validator->validate('1.42')));
$tester->assertEquals(0, count($validator->validate(1)));
$tester->assertEquals(0, count($validator->validate(5)));
$tester->assertEquals(1, count($validator->validate(0)));
$tester->assertEquals(1, count($validator->validate(-4)));
$tester->assertEquals(1, count($validator->validate('-78.1')));



// $em = $container->get(DbEntityManager::class);
// $tester->assertEquals(
//     [
//         'review' => [
//             'playable' => [
//                 'name' => 'Half-Life',
//             ],
//         ],
//     ],
//     $em->getScalarProperty('playable_name', 'Half-Life', ['review', ['playable', ['review', 'playable']]]),
// );

// $tester->assertEquals(
//     [
//         'name' => 'Georges',
//     ],
//     $em->getScalarProperty('author_name', 'Georges', [['author', []]]),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//         'review' => [
//             'playable' => [
//                 'name' => 'Half-Life',
//                 'year' => 1998,
//             ],
//             'rating' => 5,
//         ],
//         'admin' => true,
//     ],
//     $em->toAppData([
//         'author_name' => 'M. Grinchon',
//         'playable_name' => 'Half-Life',
//         'playable_year' => 1998,
//         'review_rating' => 5,
//         'admin' => true,
//     ], 'author', ['review', ['playable', ['review', 'playable']]]),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//     ],
//     $em->toAppData([
//         'author_name' => 'M. Grinchon',
//     ], 'author'),
// );

// $tester->assertEquals([
//         'name' => 'M. Grinchon',
//         'category' => [
//             'name' => 'yo',
//         ],
//         'tag' => [
//             'name' => 'hi',
//         ],
//     ],
//     $em->toAppData([
//         'name' => 'M. Grinchon',
//         'category_name' => 'yo',
//         'tag_name' => 'hi',
//     ], groups: ['category', 'tag']),
// );

if (count($tester->getErrors()) > 0) {
    var_dump($tester->getErrors());
    exit(1);
} else {
    echo "All the tests passed.\n";
    exit(0);
}