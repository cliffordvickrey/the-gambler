<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Middleware\GarbageCollectionMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;

return [
    'debug' => true,
    GameRepositoryInterface::class => [
        'gameTtl' => 10800
    ],
    GarbageCollectionMiddleware::class => [
        'randomValue' => 100
    ],
    HighScoresRepositoryInterface::class => [
        'maxHighScores' => 10
    ]
];
