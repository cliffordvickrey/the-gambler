<?php

declare(strict_types=1);

namespace Cliffordvickrey\TheGambler\Infrastructure\Factory\Infrastructure\Cache;

use Cliffordvickrey\TheGambler\Infrastructure\Cache\FileCache;
use Cliffordvickrey\TheGambler\Infrastructure\Serializer\SerializerInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;

class GameCacheFactory extends AbstractFileCacheFactory
{
    public function __invoke(ContainerInterface $container): CacheInterface
    {
        return new FileCache($this->getPath($container, 'game'), $container->get(SerializerInterface::class));
    }
}
