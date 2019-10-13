<?php

declare(strict_types=1);

use Cliffordvickrey\TheGambler\Api\Middleware\GarbageCollectionMiddleware;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoresRepositoryInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;

return [
    'apiRoot' => '',
    'debug' => false,
    FileCache::class => [
        'root' => __DIR__ . '/../../poker-cache'
    ],
    GameRepositoryInterface::class => [
        'gameTtl' => 10800
    ],
    GarbageCollectionMiddleware::class => [
        'randomValue' => 100
    ],
    HighScoresRepositoryInterface::class => [
        'maxHighScores' => 10
    ],
    'production' => true
];
