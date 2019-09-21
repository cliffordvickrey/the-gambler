<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Api\Middleware;

use Cliffordvickrey\TheGambler\Api\Middleware\GarbageCollectionMiddleware;
use Cliffordvickrey\TheGambler\Infrastructure\Cache\GameCacheInterface;
use Cliffordvickrey\TheGambler\Infrastructure\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class GarbageCollectionMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $randomValue = $config[GarbageCollectionMiddleware::class]['randomValue'] ?? null;
        return new GarbageCollectionMiddleware($container->get(GameCacheInterface::class), $randomValue);
    }
}