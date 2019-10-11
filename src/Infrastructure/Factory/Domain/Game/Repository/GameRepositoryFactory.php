<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Domain\Game\Repository;

use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepository;
use Cliffordvickrey\TheGambler\Domain\Game\Repository\GameRepositoryInterface;
use Cliffordvickrey\TheGambler\Domain\Game\Service\GameServiceInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\GameCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GameRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $gameTtl = $config[GameRepositoryInterface::class]['gameTtl'] ?? null;

        return new GameRepository(
            $container->get(GameCacheInterface::class),
            $container->get(GameServiceInterface::class),
            $gameTtl
        );
    }
}
