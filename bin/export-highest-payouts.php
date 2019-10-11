<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Domain\Probability\Generator\CombinationGenerator;
use Cliffordvickrey\TheGambler\Domain\Probability\Service\ProbabilityServiceInterface;
use Cliffordvickrey\TheGambler\Domain\Utility\HandDecorator;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Deck;
use Cliffordvickrey\TheGambler\Domain\ValueObject\Hand;
use Psr\Container\ContainerInterface;

ini_set('max_execution_time', '0');
ini_set('memory_limit', '-1');

require_once('../vendor/autoload.php');

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../app/container.php';

/** @var ProbabilityServiceInterface $probabilityService */
$probabilityService = $container->get(ProbabilityServiceInterface::class);

$deck = new Deck();
$generator = (new CombinationGenerator())($deck, Hand::HAND_SIZE);

$rows = [[
    'id',
    'hash',
    'card1',
    'card2',
    'card3',
    'card4',
    'card5',
    'meanPayout'
]];

$memo = [];
$i = 0;

foreach ($generator as $cards) {
    $hand = new Hand(...$cards);
    $handDecorator = new HandDecorator($hand);
    $hash = $handDecorator->getCanonicalHandHash();

    if (!isset($memo[$hash])) {
        $tree = $probabilityService->getCanonicalProbabilityTree($hand);
        $node = $tree->getNodeWithHighestMeanPayout();
        $memo[$hash] = $node->getMeanPayout();
    }

    $rows[] = [
        ++$i,
        $hash,
        (string)$hand->getByOffset(0),
        (string)$hand->getByOffset(1),
        (string)$hand->getByOffset(2),
        (string)$hand->getByOffset(3),
        (string)$hand->getByOffset(4),
        $memo[$hash]
    ];
}

$resource = fopen('hands.csv', 'w');
foreach ($rows as $row) {
    fputcsv($resource, $row);
}
fclose($resource);
