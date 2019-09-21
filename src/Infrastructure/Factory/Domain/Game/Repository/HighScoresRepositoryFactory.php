<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Repository\HighScoreRepository;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\HighScoresCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class HighScoresRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $maxHighScores = $config['maxHighScores'] ?? null;

        return new HighScoreRepository($container->get(HighScoresCacheInterface::class), $maxHighScores);
    }

}